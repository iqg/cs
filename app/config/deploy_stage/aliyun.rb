
default_run_options[:pty] = true
ssh_options[:forward_agent] = true

server '114.55.224.232', :app, :web, :primary => true

set :branch,      "master"
set :user,      "work"

set :deploy_to,   "/data/www/rainbow"

namespace :deploy do
    task :restart, :roles => :app, :except => { :no_release => true } do
        run "chmod -R 777 #{release_path}/app/cache/"
        run "supervisorctl -c /etc/supervisord/supervisord.conf restart php-fpm"
    end
end