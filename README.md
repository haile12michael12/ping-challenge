# Challenge 01: PHP 7.3 + gRPC Echo with React

## Description
A basic echo server using Spiral gRPC. React sends a string, PHP returns it.

## Requirements
- PHP 7.3
# Challenge 01: PHP 7.3 + gRPC Echo with React

## Description
A basic echo server using Spiral gRPC. React sends a string, PHP returns it.

## Requirements
- PHP 7.3
- Composer
- Node.js
- Docker (optional)

## Setup

### Backend
```bash
cd backend-php
composer install
php server.php
```

---

## ✅ Benefits of This Structure

- ✅ Clearly separated concerns
- ✅ Easy to test/CI each challenge independently
- ✅ Scales well
- ✅ GitHub-friendly
- ✅ Ready for deployment (Docker, CI/CD)

---

## Docker

Run the full stack (builds each service image using the Dockerfiles):

PowerShell:

```powershell
docker compose up --build -d
```

This starts three services:
- `backend` (PHP gRPC) listening on host port 50051
- `envoy` (proxy) listening on host port 3001
- `frontend` (static built site) served on host port 3000

For active frontend development with Vite hot-reload, use the development compose file which mounts source into the containers:

```powershell
docker compose -f docker-compose.dev.yml up --build
```

Tips:
- On Windows make sure Docker Desktop has file sharing enabled for your project path.
- If ports are already in use, stop the conflicting service or change the host port in `docker-compose.yml`.
- To view logs:

```powershell
docker compose logs -f
```

To stop and remove containers:

```powershell
docker compose down
```



