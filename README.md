# case-study
This is a base installation of Symfony 7, phpstan and phpunit and the starting point for your case study. We had a programmer
that started writing the code, but needed to leave for an emergency and your task is to pick up where he left off. He
did not test the code he wrote, so there may be all sorts of errors everywhere. Don't get confused by them but fix it.

## Task
This project aims to be the central controller of a system of tools (workers) that request a tasks from us, process the task and
reply with the result. A result can be an alert that needs to be further processed. For this case study, we just use 
the command "app:generate-task" to generate a task and ignore the sophisticated task generation algorithm.

The workers talk to the central controller via a REST API. The central controller stores the tasks in a mariadb database
using Doctrine ORM. The workers can request a task from the central controller and mark the task as completed or failed.
Multiple workers can request tasks concurrently, so you need to make sure that the tasks are not assigned to multiple workers,
but you can ignore race conditions for this case study.

A worker then can raise an alert because it found a problem with the task. The alert is stored in the database for
further processing. A task can fail or complete independently of the alert. If a task is run again and the alert is not raised
again, then the alert is marked as resolved. On the other hand, if a task raises an alert again, then the alert should be updated to
know when the alert was last seen.

We need the following api endpoints:
- GET  /api/task/request-task - Get a task by id
- POST /api/task/{task}/completed - Mark a task as completed
- POST /api/task/{task}/failed - Mark a task as failed
- POST /api/alert - Create an alert

The worker passes its authentication via X-Worker-Id and X-Worker-Token headers. Once authenticated, no further authorization is needed.


### Planning Phase
After reading these requirements spent a few minutes to look at the existing code and plan your approach.
- Write down what entities you need to add and how you need to change the existing ones to fit the requirements
- Think about how to implement the authentication for the workers
- Plan the project and create an estimate of how long each part will take (No need to split it into more than two or three parts)

### Technical Requirements
- Code needs to explain itself. Comment where necessary but don't overdo it
- Code readability is important. Use meaningful variable/class/method names and split your code into methods/classes where it makes sense
- Use our provided docker environment to run the project. It exposes the API on port 10001 and a phpmyadmin on 10010. (`docker compose up -d`)
- phpstan must not show any problems (`docker compose exec api vendor/bin/phpstan`)
- Routing must be done using fosrestbundle, serialization with jms/serializer
- Test the most critical parts of your code with phpunit (`docker compose exec api vendor/bin/phpunit`)
- Use doctrine migrations
BONUS:
- There are some small things in the existing code that work (or not?), but are not optimal. Find and fix them.


### No one is born a master
If you have any questions or need help, please ask us. We are a team, and we are here to help each other.

### Submitting your work
- Create a fork of this repository and push your work to it
- Prepare a small presentation of your work, explaining your approach and the challenges you faced


## Other
### Why is this document in english?
We are mostly speaking german, but we have a few non-german speaking colleagues.
To make sure that you are able to understand instructions and communicate with everyone, we decided to use english for our case studies.
Also, the code is always pure english.

### List of useful commands
- `docker compose up -d`
- `docker compose exec api bash`
- `docker compose exec api vendor/bin/phpstan`
- `docker compose exec api vendor/bin/phpunit`
- `docker compose exec api bin/console`
- `docker compose exec api test-scripts/request-job.sh`
- `docker compose exec api test-scripts/finish-job.sh`
- `docker compose exec api test-scripts/alert.sh`
