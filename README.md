# Symfony UK Usergroup Raffle

The raffle has 2 main components:

1. A web interface for entrants to enter their name and email address
1. A console command which selects entrants, shuffles them, and picks a winner at random

### Building the project

You will need Docker.

1. Clone this repo
1. Run `make up` (only tested on Linux so far)
1. Go to http://localhost:8081

If `make up` doesn't work for you, you can run the commands in the `up` section of the `Makefile` individually.

If DynamoDB gives you errors, run `make down`, delete the `docker/dynamodb` directory (and the `.db` file inside it),
then run `make up` again.