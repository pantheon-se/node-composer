[![License](https://img.shields.io/packagist/l/pantheon-se/node-composer)](LICENSE) [![Packagist Version](https://img.shields.io/packagist/v/pantheon-se/node-composer)](https://packagist.org/packages/pantheon-se/node-composer) [![Tests](https://github.com/pantheon-se/node-composer/workflows/Tests/badge.svg?branch=master)](https://github.com/pantheon-se/node-composer/actions?query=workflow%3ATests)

# Node Composer

> Composer Plugin to implement asset compilation via Composer with Node.js.

Based on [node-composer by mariusbuescher](https://github.com/mariusbuescher/node-composer), this Composer plugin will install Node.js, NPM, and/or Yarn into your vendor/bin directory so that they are available to use during your Composer builds. This plugin helps automate the download of the binaries which are linked to the bin-directory specified in your composer.json.

Once installed, you can then use Node, NPM, and Yarn commands in your composer-scripts.

## Setup

Simply install the plugin, and the latest Node.js LTS with NPM will be installed - **no other configurations are necessary**. Optionally, you can specify the `node-version` in your composer.json extra configs to declare a specific version of Node.js. For Yarn, `yarn-version` can either be set to `true` to install the latest, or can be set to a specific version.

**Example composer.json with Yarn**

```json
{
  "name": "my/project",
  "type": "project",
  "license": "MIT",
  "require": {
    "pantheon-se/node-composer": "*"
  },
  "extra": {
    "pantheon-se": {
      "node-composer": {
        "yarn-version": true
      }
    }
  },
  "config": {
    "allow-plugins": {
      "pantheon-se/node-composer": true
    }
  }
}
```

## Configuration

There are three parameters you can configure: 
- Node version (`node-version`)
- Yarn version (`yarn-version`)
- The download url template for the Node.js binary archives (`node-download-url`).

In the Node download url, replace the following placeholders with your specific needs:

- version: `${version}`
- type of your os: `${osType}`
- system architecture: `${architecture}`
- file format `${format}`

**Example composer.json with specific versions of Node and Yarn** 

```json
{
  "extra": {
    "pantheon-se": {
      "node-composer": {
        "node-version": "16.14.0",
        "yarn-version": "1.22.18",
        "node-download-url": "https://nodejs.org/dist/v${version}/node-v${version}-${osType}-${architecture}.${format}"
      }
    }
  }
}
```
