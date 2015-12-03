A RETFull Api Sturvture 
==========

Reads annotations from php files and generates API code.

Example Project
---------------

There is an example project in the example directory.

### Building the example project

The example project can be run by issuing the following commands in the root:

    # install dependencies
    make install

    # run the build
    make

### Build contents

A build folder will be created at `out/build` with the following structure:

    build/
      controllers/
        Api/
            HomeController.php
            Account/
              AccountController.php
        docs/
            api-json.js

The `docs/api-json.js` file will be copied to the `src/Api/public/js` folder.

### Example API Documentation

The example documentation can be viewed by opening the
`src/Api/public/index.html` file in a web browser.

How it works
------------

- read annotations in a php class
- export to the desired format

Exporters
---------

### JsonApiDoc

Exports a JSON object to a format used to generate html documentation.

### Silex

Exports PHP controllers written in a format for a Silex app.

Dependencies
------------

- composer to install the following:
  - symfony/yaml
  - twig/twig
  - doctrine/common (for annotations)
  - silex/silex
