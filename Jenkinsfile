pipeline {
    agent any

    stages {
        stage('Clone') {
            agent {
                docker {
                  image 'composer:1.8'
                  reuseNode true
                }
            }
            steps {
                sh script: "sh ./dev/setup/src/clone.sh", label: "Clone Repositories"
            }
        }
        stage('Install Dependencies') {
            agent {
                docker {
                  image 'composer:1.8'
                  reuseNode true
                }
            }
            steps {
                sh script: "composer install", label: "Install WP and required plugins"
                sh script: "sh ./dev/setup/src/composer.sh", label: "Install Composer Dependencies"
                sh script: "sh ./dev/setup/src/submodules.sh", label: "Install Git Submodules"
            }
        }
        stage('Build JS') {
            agent {
                docker {
                    image 'node:10.15.0-alpine'
                    args '-u root'
                    reuseNode true
                }
            }
            steps {
                sh script: "sh ./dev/setup/src/npm.sh", label: "Install NPM Dependencies"
                sh script: "sh ./dev/setup/src/gulp.sh", label: "Compile Javascript and CSS"
            }
        }
        stage('Test Config') {
            steps {

            }
        }
        stage('WPUnit') {
            steps {

            }
        }
    }
}
