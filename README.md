# Computer Science Visual Assistant (CSVA)

## Clone Project
```bash
git clone https://github.com/panuwit89/csva.git
```
```bash
cd csva
```
```bash
git checkout develop
```
```bash
git flow init -d
```

## Setup Project
### Run Docker
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```
### Copy .env file
```bash
cp .env.example .env
```
### Compose Up Project
```bash
sail up -d
```
```bash
sail artisan key:generate
```
```bash
sail artisan migrate
```
### Install NPM
```bash
npm install
```
```bash
npm run dev
```
### Compose Down Project
```bash
sail down
```
### Access Laravel Project
> http://localhost
### Access phpMyAdmin
> http://localhost:8080

## Run Seeder
```bash
sail artisan migrate:fresh --seed
```
