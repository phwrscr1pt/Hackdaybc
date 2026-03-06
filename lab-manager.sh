#!/bin/bash
# Lab Manager Script for LeaguesOfCode

cd /home/loc/HackdayBc

case "$1" in
  status)
    echo "=== Container Status ==="
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
    ;;
    
  test)
    echo "=== Testing All Endpoints ==="
    for path in "/" "/members/" "/sqli/" "/jwt/" "/profile/" "/share/" "/evil/" "/api/" "/search/"; do
      CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost$path)
      if [ "$CODE" = "200" ]; then
        echo "[OK] $path"
      else
        echo "[FAIL] $path (HTTP $CODE)"
      fi
    done
    echo ""
    echo "=== Testing SSH Lab ==="
    nc -zv localhost 2222 2>&1 | grep -q succeeded && echo "[OK] SSH Lab (port 2222)" || echo "[FAIL] SSH Lab"
    ;;
    
  reset)
    echo "=== Full Reset (with database) ==="
    docker-compose down -v
    docker-compose up -d
    echo "Waiting for services to start..."
    sleep 10
    $0 test
    ;;
    
  reset-db)
    echo "=== Reset Database Only ==="
    docker-compose rm -sf db
    docker volume rm hackdaybc_mysql_data 2>/dev/null
    docker-compose up -d db
    sleep 10
    docker-compose restart portal
    ;;
    
  reset-ssh)
    echo "=== Reset SSH Lab ==="
    docker-compose up -d --force-recreate ssh-lab
    ;;
    
  reset-uploads)
    echo "=== Clear File Uploads ==="
    rm -rf ./labs/hackday-afu/uploads/*
    docker-compose restart file-upload
    ;;
    
  logs)
    docker-compose logs -f ${2:-}
    ;;
    
  stop)
    echo "=== Stopping All Labs ==="
    docker-compose down
    ;;
    
  start)
    echo "=== Starting All Labs ==="
    docker-compose up -d
    ;;
    
  *)
    echo "Lab Manager - LeaguesOfCode"
    echo ""
    echo "Usage: $0 <command>"
    echo ""
    echo "Commands:"
    echo "  status        Show container status"
    echo "  test          Test all lab endpoints"
    echo "  reset         Full reset (containers + database)"
    echo "  reset-db      Reset only database"
    echo "  reset-ssh     Reset only SSH lab"
    echo "  reset-uploads Clear uploaded files"
    echo "  logs [name]   View logs (optional: container name)"
    echo "  start         Start all labs"
    echo "  stop          Stop all labs"
    ;;
esac
