pipeline {
    agent any

    stages {
        stage('Clone') {
            steps {
                sh script: "sh ./dev/setup/src/clone.sh", label: "Clone Repositories"
            }
        }
        stage('Install Dependencies') {
            steps {
                sh script: "composer install", label: "Install WP and required plugins"
                sh script: "sh ./dev/setup/src/composer.sh", label: "Install Composer Dependencies"
                sh script: "sh ./dev/setup/src/submodules.sh", label: "Install Git Submodules"
            }
        }
        stage('Build JS') {
            steps {
                sh script: "sh ./dev/setup/src/npm.sh", label: "Install NPM Dependencies"
                sh script: "sh ./dev/setup/src/gulp.sh", label: "Compile Javascript and CSS"
            }
        }
    }
}
