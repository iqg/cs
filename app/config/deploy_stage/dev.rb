server '10.0.0.10', :app, :web, :primary => true

set :branch,	"dev"

set :deploy_to,   "/var/www/dev.cs.lab"
set :clear_controllers,     false
