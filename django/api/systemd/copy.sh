#!/bin/bash

sudo cp ./gunicorn.service /etc/systemd/system/gunicorn.service
sudo systemctl daemon-reload
sudo systemctl enable gunicorn.service
sudo systemctl restart gunicorn.service