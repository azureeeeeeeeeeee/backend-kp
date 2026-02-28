# 🚀 VPS Deployment Guide

Step-by-step guide to deploy the **Backend KP** Laravel application on a VPS using Docker.

---

## Prerequisites

- VPS with Ubuntu 22.04+ (minimum 1GB RAM, 1 vCPU)
- Domain name pointed to your VPS IP (optional, for SSL)
- SSH access to the server

---

## 1. Server Setup

### Install Docker & Docker Compose

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com | sh

# Add your user to the docker group (so you don't need sudo)
sudo usermod -aG docker $USER

# Apply group changes (or log out and back in)
newgrp docker

# Verify installation
docker --version
docker compose version
```

### Install Git

```bash
sudo apt install git -y
```

---

## 2. Clone the Repository

```bash
cd /home/$USER
git clone <YOUR_REPO_URL> backend-kp
cd backend-kp
```

---

## 3. Configure Environment

```bash
# Copy the example environment file
cp .env.example .env
```

Edit `.env` with your production values:

```bash
nano .env
```

**Important values to update:**

```env
APP_NAME="Backend KP"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

BASE_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=backend_kp
DB_USERNAME=root
DB_PASSWORD=your_strong_password_here

FE_URL=https://your-frontend-domain.com
```

> **⚠️ Important:** Never use `APP_DEBUG=true` or weak passwords in production!

---

## 4. Build & Start Containers

```bash
# Build and start all services in detached mode
docker compose up -d --build

# Check that all containers are running
docker compose ps
```

You should see 4 containers running:
- `backend-kp-app` (PHP-FPM)
- `backend-kp-nginx` (Web server)
- `backend-kp-db` (MySQL)
- `backend-kp-phpmyadmin` (Database admin)

---

## 5. Application Setup

Run these commands inside the app container:

```bash
# Generate application key
docker compose exec app php artisan key:generate

# Run database migrations
docker compose exec app php artisan migrate --force

# (Optional) Run seeders if needed
docker compose exec app php artisan db:seed --force

# Create storage symlink
docker compose exec app php artisan storage:link

# Optimize for production
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

---

## 6. Verify Deployment

Open your browser and check:

| URL | Purpose |
|-----|---------|
| `http://YOUR_IP` | Main API |
| `http://YOUR_IP/api/documentation` | Swagger docs |
| `http://YOUR_IP:8080` | phpMyAdmin |

---

## 7. SSL Setup (Optional but Recommended)

If you have a domain name, set up free SSL with Certbot:

### Install Certbot

```bash
sudo apt install certbot -y
```

### Get SSL Certificate

```bash
# Stop nginx temporarily to free port 80
docker compose stop nginx

# Get the certificate
sudo certbot certonly --standalone -d yourdomain.com

# Restart nginx
docker compose start nginx
```

### Update Nginx for SSL

Create a new nginx config at `nginx/default.conf`:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl;
    server_name yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    root /var/www/html/public;
    index index.php index.html;

    client_max_body_size 100M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        include fastcgi_params;
        fastcgi_buffering off;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Then update `docker-compose.yml` nginx service to mount the SSL certs:

```yaml
  nginx:
    image: nginx:alpine
    container_name: backend-kp-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
      - .:/var/www/html
      - /etc/letsencrypt:/etc/letsencrypt:ro
    depends_on:
      - app
    networks:
      - laravel
```

Restart the containers:

```bash
docker compose up -d --build
```

### Auto-Renew SSL

```bash
# Add cron job for auto-renewal
(crontab -l 2>/dev/null; echo "0 0 1 * * certbot renew --pre-hook 'docker compose -f /home/$USER/backend-kp/docker-compose.yml stop nginx' --post-hook 'docker compose -f /home/$USER/backend-kp/docker-compose.yml start nginx'") | crontab -
```

---

## 8. Common Commands

```bash
# View logs
docker compose logs -f app
docker compose logs -f nginx

# Restart all containers
docker compose restart

# Stop all containers
docker compose down

# Stop and remove volumes (⚠️ deletes database!)
docker compose down -v

# Run artisan commands
docker compose exec app php artisan <command>

# Access MySQL CLI
docker compose exec db mysql -u root -p

# Rebuild after code changes
git pull origin main
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

---

## 9. Troubleshooting

### Container won't start
```bash
# Check logs for errors
docker compose logs app
docker compose logs db
```

### Permission issues
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Database connection refused
Make sure the `db` container is healthy first:
```bash
docker compose ps
# If db shows "unhealthy", wait or check db logs:
docker compose logs db
```

### Clear all caches
```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

### Updating the application
```bash
cd /home/$USER/backend-kp
git pull origin main
docker compose up -d --build
docker compose exec app php artisan migrate --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```
