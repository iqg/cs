server '10.0.0.10', :app, :web, :primary => true

set :branch,      "online"
set :user,      "work"

set :deploy_to,   "/var/www/online.cs.lab"
