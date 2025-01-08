#!/bin/bash

if [ $# -ne 4 ]; then
    echo "Usage: $0 <worker-id> <worker-access-token> <task-id> <completed|failed>"
    exit 1
fi

curl -X POST -H "X-Worker-Id: $1" -H "X-Worker-Token: $2" "http://localhost/api/task/$3/$4"
