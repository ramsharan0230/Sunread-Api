server '94.237.40.213',
user: 'deployer',
roles: %w{web app},
port:22,
ssh_options: {
    forward_agent: true,
    auth_methods: %w(publickey)
}

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, "/home/deployer/api"

set  :tmp_dir, '/home/deployer/tmp'

set :keep_releases, 1

# Share files/directories between releases
set :linked_files, [".env"]
set :linked_dirs, ["storage"]


set :composer_install_flags, '--no-dev --no-interaction --quiet --optimize-autoloader'
set :composer_roles, :all
set :composer_working_dir, '/home/deployer/api/current'
set :composer_dump_autoload_flags, '--optimize'
set :composer_download_url, "https://getcomposer.org/installer"
set :composer_version, '1.0.0-alpha8' #(default: not set)

Rake::Task['deploy:updated'].prerequisites.delete('composer:install')


task :update_composer do
  invoke "composer:run", :update, "--dev --prefer-dist"
end

task :reload_supervisor do
    puts "==================restart supervisor======================"
    on roles(:all) do
        execute :supervisorctl, "reread"
        execute :supervisorctl, "update"
    end
end

#after "deploy:published", "update_composer"
#after "deploy:published", "reload_supervisor"
