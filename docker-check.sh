#!/bin/bash

# Docker Environment Check Script
# Verifies Docker daemon is running and required ports are available

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REQUIRED_PORTS=(3306 6379)

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "🔍 Checking Docker environment prerequisites..."
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}❌ Error: Docker is not installed.${NC}"
    echo "Please install Docker Desktop from https://www.docker.com/products/docker-desktop"
    exit 1
fi

# Check if Docker daemon is running
if ! docker info &> /dev/null; then
    echo -e "${RED}❌ Error: Docker daemon is not running.${NC}"
    echo "Please start Docker Desktop and try again."
    exit 1
fi

echo -e "${GREEN}✓ Docker daemon is running${NC}"

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    echo -e "${RED}❌ Error: docker-compose is not available.${NC}"
    echo "Please ensure Docker Compose is installed."
    exit 1
fi

echo -e "${GREEN}✓ Docker Compose is available${NC}"

# Check for port conflicts
PORT_CONFLICTS=()

for port in "${REQUIRED_PORTS[@]}"; do
    if [[ "$OSTYPE" == "darwin"* ]] || [[ "$OSTYPE" == "linux-gnu"* ]]; then
        # macOS and Linux
        if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1 ; then
            PORT_CONFLICTS+=($port)
        fi
    elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "win32" ]]; then
        # Windows (Git Bash)
        if netstat -ano | findstr ":$port.*LISTENING" > /dev/null 2>&1; then
            PORT_CONFLICTS+=($port)
        fi
    fi
done

if [ ${#PORT_CONFLICTS[@]} -gt 0 ]; then
    echo -e "${RED}❌ Error: Port conflict detected${NC}"
    echo ""
    echo "The following required ports are already in use:"
    for port in "${PORT_CONFLICTS[@]}"; do
        echo "  - Port $port"
        if [[ "$port" == "3306" ]]; then
            echo "    (MySQL - Check if you have MySQL running locally)"
        elif [[ "$port" == "6379" ]]; then
            echo "    (Redis - Check if you have Redis running locally)"
        fi
    done
    echo ""
    echo "Solutions:"
    echo "  1. Stop the services using these ports"
    echo "  2. Modify docker-compose.yml to use different ports"
    echo ""
    exit 1
fi

echo -e "${GREEN}✓ All required ports (${REQUIRED_PORTS[*]}) are available${NC}"
echo ""
echo -e "${GREEN}✅ All checks passed! You can start Docker services with:${NC}"
echo "   docker-compose up -d"
echo ""

exit 0
