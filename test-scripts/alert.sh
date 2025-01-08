#!/bin/bash

if [ $# -ne 4 ]; then
    echo "Usage: $0 <worker-id> <worker-access-token> <task-id> <alert-type>"
    exit 1
fi

# add json
curl -X POST -H "X-Worker-Id: $1" -H "X-Worker-Token: $2" http://localhost/api/alert -d "{\"task_id\": \"$3\", \"alert_type: \"$4\", \"message\": \"Alert message\", \"reason\": \"Reason\"}"
