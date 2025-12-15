# Deploying Service Pro to Amazon EC2 (Amazon Linux 2023) with Docker

This guide explains how to deploy your Laravel + Vue.js application to an AWS EC2 instance (running **Amazon Linux 2023**) using Docker.

## 1. Prerequisites

- An **AWS Account**.
- An **EC2 Instance** running **Amazon Linux 2023**.
- **SSH Access** to your instance (using your `.pem` key).
- Ports **80 (HTTP)** and **22 (SSH)** opened in your EC2 Security Group.

## 2. Connect to your EC2 Instance

Open your terminal and SSH into your server (default user is `ec2-user`):

```bash
ssh -i "path/to/your-key.pem" ec2-user@your-ec2-public-ip
```

## 3. Install Docker & Docker Compose (Amazon Linux 2023)

Run the following commands on your EC2 instance:

```bash
# 1. Update the system
sudo dnf update -y

# 2. Install Docker and Git
sudo dnf install -y docker git

# 3. Start Docker service and enable it to start on boot
sudo systemctl start docker
sudo systemctl enable docker

# 4. Add 'ec2-user' to the docker group (to run docker without sudo)
sudo usermod -aG docker ec2-user

# 5. Install Docker Compose (V2 Plugin)
mkdir -p ~/.docker/cli-plugins/
curl -SL https://github.com/docker/compose/releases/latest/download/docker-compose-linux-x86_64 -o ~/.docker/cli-plugins/docker-compose
chmod +x ~/.docker/cli-plugins/docker-compose

# 6. Verify installation
docker compose version
```

**IMPORTANT**: You must **log out** and **log back in** for the group changes to take effect:
```bash
exit
# Then ssh again
ssh -i "key.pem" ec2-user@your-ip
```

## 4. Deploy the Application

### Option A: Using Git (Recommended)

1.  **Clone your repository** to the EC2 instance:
    ```bash
    git clone https://github.com/your-username/service_pro.git
    cd service_Pro
    ```

### Option B: Copy Files Manually (If no Git)

From your **local machine**, use `scp` to copy your project:

```bash
scp -i "key.pem" -r ./service_Pro ec2-user@your-ip:~/service_Pro
```

## 5. Environment Configuration

1.  Navigate to the backend directory and set up your `.env`:
    ```bash
    cd service_Pro/backend
    cp .env.example .env
    nano .env
    ```
2.  Update the database settings in `.env`.
    **Important**: In Docker, the DB host is `db`, not `127.0.0.1` or `localhost`.
    ```env
    DB_CONNECTION=mysql
    DB_HOST=db
    DB_PORT=3306
    DB_DATABASE=service_pro
    DB_USERNAME=root
    DB_PASSWORD=rootpassword
    ```

## 6. Start the Application

Return to the root directory (where `docker-compose.yml` is) and launch the containers:

```bash
cd ~/service_Pro
docker compose up -d --build
```

This command will:
- Build the Frontend (Vue) and Backend (Laravel) images.
- Start MySQL, Nginx for Backend, phpMyAdmin, and the App.
- Expose the **Frontend on Port 80** and **phpMyAdmin on Port 8081**.

## 7. Post-Deployment Steps

1.  **Install Vendor Dependencies** (if needed):
    ```bash
    docker compose exec app composer install
    ```

2.  **Run Migrations**:
    Initialize your database tables.
    ```bash
    docker compose exec app php artisan migrate --force
    ```
    *Note: If you want to seed data, use `docker compose exec app php artisan db:seed`*

3.  **Generate App Key**:
    ```bash
    docker compose exec app php artisan key:generate
    ```

4.  **Set Permissions**:
    Ensure the storage directory is writable.
    ```bash
    docker compose exec app chown -R www-data:www-data /var/www/storage
    ```

## 8. Access Your App

Open your browser:
- **App**: `http://your-ec2-public-ip`
- **phpMyAdmin**: `http://your-ec2-public-ip:8081`

## Troubleshooting

- **Check Logs**: `docker compose logs -f`
- **Rebuild**: `docker compose up -d --build`
