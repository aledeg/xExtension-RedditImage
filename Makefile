##@ General

.PHONY: help
help: ## Display this help.
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_0-9-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

##@ Setting environment
.PHONY: install
install: ## Install the dev environment (phpunit, phpstan, ...)
	@docker-compose run composer install

.PHONY: classmap
classmap: ## Refresh classmap for autoloading
	@docker-compose run composer dump-autoload

##@ Testing
.PHONY: coding-style
coding-style: classmap ## Run the coding style fixer on all PHP files
	@docker-compose run phpcsfixer fix --allow-risky yes

.PHONY: static-analysis
static-analysis: classmap ## Run the static analysis on all PHP files
	@docker-compose run phpstan analyse --memory-limit 512M

.PHONY: test-testsuite
test-testsuite: classmap
	@docker-compose run phpunit --testsuite $(TESTSUITE)

.PHONY: test-default
test-default: TESTSUITE=default
test-default: test-testsuite ## Run the default testsuite

.PHONY: test-canary
test-canary: TESTSUITE=canary
test-canary: test-testsuite ## Run the canary testsuite
