pipeline {
    agent any

    stages {
        stage('Meta Repo Composer') {
                    steps {
                        sh script: "composer install", label: "Install WP and required plugins"
                    }
                }
        stage('Checkout Plugins') {
            steps {
                sh script: "sh ./dev/setup/src/clone.sh", label: "Clone Repositories"
            }
        }
        stage('Composer') {
            steps {
                sh script: "sh ./dev/setup/src/composer.sh", label: "Install Composer Dependencies"
            }
        }
        stage('Submodules') {
            steps {
                sh script: "sh ./dev/setup/src/submodules.sh", label: "Install Git Submodules"
            }
        }
        stage('NPM') {
            steps {
                sh script: "sh ./dev/setup/src/npm.sh", label: "Install NPM Dependencies"
            }
        }
        stage('Gulp') {
            steps {
                sh script: "sh ./dev/setup/src/gulp.sh", label: "Compile Javascript and CSS"
            }
        }
        stage('Test Config') {
            steps {
                sh script: "sh ./dev/setup/src/config.sh", label: "Setup Test configurations"
            }
        }
        stage('WPUnit') {
            steps {
                sh script: "sh ./dev/setup/src/tests.sh", label: "Run Tests"
            }
        }
    }
}
