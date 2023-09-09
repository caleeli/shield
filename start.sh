#!/bin/bash
# Get user and group ids of the current file for APACHE_RUN_USER and APACHE_RUN_GROUP
export APACHE_RUN_USER="#$(stat -c '%u' .)"
export APACHE_RUN_GROUP="#$(stat -c '%g' .)"
# Start the server
docker compose up --build
