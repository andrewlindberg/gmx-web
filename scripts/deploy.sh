#!/bin/bash
php scripts/manifest.php 0.1.$(git rev-list --no-merges --count HEAD ^master)-alpha
zip -9 -r -q --exclude=".idea/*" --exclude=".git/*" --exclude=".gitignore" --exclude=".gitkeep" --exclude="runtime/*" --exclude="uploads/*" --exclude="config.php" --exclude="router.php" --exclude="manifest.php" --exclude=".travis.yml" --exclude="README.md" --exclude="scripts/*" gmx-web.zip .