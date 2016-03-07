server '10.0.0.10', :app, :web, :primary => true

set :branch,      "staging"

set :deploy_to,   "/var/www/staging.cs.lab"
