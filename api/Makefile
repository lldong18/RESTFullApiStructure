COMPOSER=composer
PHP=php
PROJECT_DIR = $(PWD)
API_SRC_DIR=$(PROJECT_DIR)/src
BUILD_DIR=$(API_SRC_DIR)/out/build
PHPMD=vendor/bin/phpmd
PHPCS=vendor/bin/phpcs
PHPUNIT=vendor/bin/phpunit
API_CREATOR_DIR=bin/ApiCreator
TEST_DIR=tests

all: run lint test

run: clean
	$(PHP) $(API_SRC_DIR)/build.php
	cp $(PROJECT_DIR)/out/build/docs/api.json.js $(API_SRC_DIR)/public/js/api.json.js

lint:
	for i in `find $(BUILD_DIR) -name '*.php'`; do $(PHP) -l $$i; done
	$(PHPMD) $(API_SRC_DIR),$(TEST_DIR) text config/phpmd.xml
	$(PHPCS) --standard=PSR2 $(API_CREATOR_DIR) $(TEST_DIR)

clean:
	echo $(PROJECT_DIR)
	rm -fr $(BUILD_DIR)

install:
	$(COMPOSER) install

update:
	$(COMPOSER) update

test:
	$(PHPUNIT)

watch:
	$(PHP) bin/watch.php
