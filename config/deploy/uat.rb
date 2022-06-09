server '5.22.211.155',
user: 'deployer',
roles: %w{web app},
port:22,
ssh_options: {
    forward_agent: true,
    auth_methods: %w(publickey)
}

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, "/home/deployer/api"

set :tmp_dir, '/home/deployer/tmp'

set :keep_releases, 1

# Share files/directories between releases
set :linked_files, [".env"]
set :linked_dirs, ["storage"]

set :composer_install_flags, '--no-dev --no-interaction --quiet --optimize-autoloader'
set :composer_roles, :all
set :composer_working_dir, '/home/deployer/api/current'
set :composer_dump_autoload_flags, '--optimize'
set :composer_download_url, "https://getcomposer.org/installer"
# set :composer_version, '1.0.0-alpha8' #(default: not set)

Rake::Task['deploy:updated'].prerequisites.delete('composer:install')

task :update_composer do
	invoke "composer:run", :update, "--prefer-dist --ignore-platform-reqs"
end

task :install_composer do
  invoke "composer:run", :install, "--prefer-dist --ignore-platform-reqs"
end

task :get_geoip_files do
    puts "==================Get Composer and Maxmind file======================"
    on roles(:all) do
        execute "cd ~/api/current && curl -sS https://getcomposer.org/installer | php"
        execute "cd ~/api/current && php composer.phar require geoip2/geoip2:~2.0 --ignore-platform-reqs"
        execute "cd ~/api/current && composer dump-autoload"
    end
end

task :reload_supervisor do
    puts "==================restart supervisor======================"
    on roles(:all) do
        execute :sudo, :supervisorctl, "restart php_serve"
        execute :sudo, :supervisorctl, "restart php_queue:*"
        execute :sudo, :supervisorctl, "restart php_schedule"
        execute :sudo, :supervisorctl, "reread"
        execute :sudo, :supervisorctl, "update"
    end
end

task :link_storage do
    puts "==================link storage======================"
    on roles(:all) do
        execute "cd ~/api/current && php artisan storage:link"
    end
end

after "deploy:published", "install_composer"
after "deploy:published", "get_geoip_files"
after "deploy:published", "reload_supervisor"
after "deploy:published", "link_storage"


# server-based syntax
# ======================
# Defines a single server with a list of roles and multiple properties.
# You can define all roles on a single server, or split them:

# server "example.com", user: "deploy", roles: %w{app db web}, my_property: :my_value
# server "example.com", user: "deploy", roles: %w{app web}, other_property: :other_value
# server "db.example.com", user: "deploy", roles: %w{db}



# role-based syntax
# ==================

# Defines a role with one or multiple servers. The primary server in each
# group is considered to be the first unless any hosts have the primary
# property set. Specify the username and a domain or IP for the server.
# Don't use `:all`, it's a meta role.

# role :app, %w{deploy@example.com}, my_property: :my_value
# role :web, %w{user1@primary.com user2@additional.com}, other_property: :other_value
# role :db,  %w{deploy@example.com}



# Configuration
# =============
# You can set any configuration variable like in config/deploy.rb
# These variables are then only loaded and set in this stage.
# For available Capistrano configuration variables see the documentation page.
# http://capistranorb.com/documentation/getting-started/configuration/
# Feel free to add new variables to customise your setup.



# Custom SSH Options
# ==================
# You may pass any option but keep in mind that net/ssh understands a
# limited set of options, consult the Net::SSH documentation.
# http://net-ssh.github.io/net-ssh/classes/Net/SSH.html#method-c-start
#
# Global options
# --------------
#  set :ssh_options, {
#    keys: %w(/home/user_name/.ssh/id_rsa),
#    forward_agent: false,
#    auth_methods: %w(password)
#  }
#
# The server-based syntax can be used to override options:
# ------------------------------------
# server "example.com",
#   user: "user_name",
#   roles: %w{web app},
#   ssh_options: {
#     user: "user_name", # overrides user setting above
#     keys: %w(/home/user_name/.ssh/id_rsa),
#     forward_agent: false,
#     auth_methods: %w(publickey password)
#     # password: "please use keys"
#   }
