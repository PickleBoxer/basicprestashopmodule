help:
	@egrep "^#" Makefile

# target: docker-build|db               - Setup/Build PHP & (node)JS dependencies
db: docker-build
docker-build: build-back build-front

build-back:
	docker-compose run --rm php sh -c "composer install"

build-back-prod:
	docker-compose run --rm php sh -c "composer install --no-dev -o"

build-front:
	docker-compose run --rm node sh -c "npm i --prefix ./_dev/js/front"
	docker-compose run --rm node sh -c "npm run build --prefix ./_dev/js/front"

# target: watch-front                   - Watcher for the vueJS files
watch-front:
	docker-compose run --rm node sh -c "npm run watch --prefix ./_dev/js/front"

# target: test-front                   - Launch the front test suite
test-front:
	docker-compose run --rm node sh -c "npm test --prefix ./_dev/js/front"

build-zip:
	cp -Ra $(PWD) /tmp/basicprestashopmodule
	rm -rf /tmp/basicprestashopmodule/.env.test
	rm -rf /tmp/basicprestashopmodule/.php_cs.*
	rm -rf /tmp/basicprestashopmodule/.travis.yml
	rm -rf /tmp/basicprestashopmodule/cloudbuild.yaml
	rm -rf /tmp/basicprestashopmodule/composer.*
	rm -rf /tmp/basicprestashopmodule/package.json
	rm -rf /tmp/basicprestashopmodule/.npmrc
	rm -rf /tmp/basicprestashopmodule/package-lock.json
	rm -rf /tmp/basicprestashopmodule/.gitignore
	rm -rf /tmp/basicprestashopmodule/deploy.sh
	rm -rf /tmp/basicprestashopmodule/.editorconfig
	rm -rf /tmp/basicprestashopmodule/.git
	rm -rf /tmp/basicprestashopmodule/.github
	rm -rf /tmp/basicprestashopmodule/_dev
	rm -rf /tmp/basicprestashopmodule/tests
	rm -rf /tmp/basicprestashopmodule/docker-compose.yml
	rm -rf /tmp/basicprestashopmodule/Makefile
	mv -v /tmp/basicprestashopmodule $(PWD)/basicprestashopmodule
	zip -r basicprestashopmodule.zip basicprestashopmodule
	rm -rf $(PWD)/basicprestashopmodule

# target: build-zip-prod                   - Launch prod zip generation of the module (will not work on windows)
build-zip-prod: build-back-prod test-front build-front build-zip