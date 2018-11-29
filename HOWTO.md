# Running the application

1. Go to your github account's settings and in "Developer settings" create a new OAuth app
2. Copy the app's ClientID and ClientSecret in the `.env` file, as well as your username and repositories you want to make accessible
3. Set in the app the Homepage url and Authorization callback URL (in case of testing it locally: `localhost:9090`)
4. Execute `./run.sh` and go to localhost:9090

Link to deployed app: https://centra-backend.herokuapp.com/
