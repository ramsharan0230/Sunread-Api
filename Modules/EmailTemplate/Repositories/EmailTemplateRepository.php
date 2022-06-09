<?php

namespace Modules\EmailTemplate\Repositories;

use Illuminate\Validation\ValidationException;
use Modules\Core\Repositories\BaseRepository;
use Modules\Core\Repositories\ConfigurationRepository;
use Modules\EmailTemplate\Entities\EmailTemplate;
use Exception;

class EmailTemplateRepository extends BaseRepository
{
    protected $config_variable, $config_template, $configurationRepository;

    public function __construct(EmailTemplate $emailTemplate, ConfigurationRepository $configurationRepository)
    {
        $this->model = $emailTemplate;
        $this->model_key = "email_template";
        $this->config_variable = config("email_variable");
        $this->config_template = config("email_template");

        $this->rules = [
            "name" => "required",
            "subject" => "required",
            "content" => "required",
            "email_template_code" => "required",
            "style" => "sometimes",
        ];
        $this->configurationRepository = $configurationRepository;
    }

    /**
     * get template group data from configuration file
     */
    public function getConfigGroup(object $request): array
    {
        try {
            $config_data = $this->config_template;

            $templates = $this->fetchAll($request, callback: function () {
                return $this->model->select("id", "name", "email_template_code");
            });

            $merged = collect($config_data)->map(function ($value) use ($templates) {
                foreach ($templates as $array) {
                    if ($value["code"] == $array["email_template_code"]) {
                        $value["templates"][] = $array;
                    }
                }
                return $value;
            })->toArray();
        } catch (Exception $exception) {
            throw $exception;
        }

        return $merged;
    }

    /**
     * get all template variables by email_template_code from configuration file
     */
    public function getConfigVariable(object $request): array
    {
        try {
            $elements = $this->config_variable;

            foreach ($elements as $element) {
                $parent = [];
                foreach ($element["variables"] as $variable) {
                    if (in_array($request->email_template_code, $variable["availability"]) || $variable["availability"] == ["all"]) {

                        unset($variable["availability"], $variable["source"], $variable["type"]);

                        $parent["label"] = $element["label"];
                        $parent["code"] = $element["code"];
                        $parent["variables"][] = $variable;
                    }
                }
                if (!empty($parent)) $data["groups"][] = $parent;
            }
        } catch (Exception $exception) {
            throw $exception;
        }

        return array_filter($data);
    }

    /**
     *  validate template group
     */
    public function templateGroupValidation(object $request): void
    {
        $all_groups = collect($this->config_template)->pluck("code")->toArray();
        if (!in_array($request->email_template_code, $all_groups)) throw ValidationException::withMessages(["email_template_code" => __("Invalid Template Code")]);
    }

    /**
     *  validate template group
     */
    public function templateVariableValidation(object $request): void
    {
        $check_braces = $this->checkBraces($request->content);
        if(!$check_braces)  throw ValidationException::withMessages(["content" => __("Invalid template content")]);

        $variables = $this->getConfigVariable($request);

        /**
         * get config variable and make it in array.
        */
        $config_variables = collect($variables)->flatten(1)->map(function ($data) {
            return $data["variables"];
        })->toArray();
        $config_variables = call_user_func_array("array_merge", $config_variables);

        /**
         * get variables used in template content with prefix of "{{$variables_name}}.
        */
        preg_match_all("/{{(.*)\}}/U", $request->content, $matches);

        /**
         * check variable exist or not.
        */
        foreach ($matches[1] as $v) {
            if (str_contains($v, "\$")) {
                /**
                 * remove 1st character. eg: remove "$" sign from variable.
                */
                $variable = substr($v, 1);

                if (!collect($config_variables)->contains("variable", $variable)) throw ValidationException::withMessages(["content" => __("Variable not found")]);
            }
        }
    }

    /**
     * check opening braces and closing braces from template content
    */
    public function checkBraces(string $str): bool
    {
        $strlen = strlen($str); // cache string length for performance
        $openbraces = 0;

        for ($i = 0; $i < $strlen; $i++)
        {
            $c = $str[$i];
            if ($c == '{') {
                // count opening bracket
                $openbraces++;
            }

            if ($c == '}') {
                // count closing bracket
                $openbraces--;
            }
            if ($openbraces < 0)
            {
                // check for unopened closing brackets
                return false;
            }
        }

        return $openbraces == 0; // check for unclosed open brackets
    }
}
