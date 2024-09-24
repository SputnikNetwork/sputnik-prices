![photo_2021-07-09_09-14-10](https://user-images.githubusercontent.com/38581319/125038896-37c05580-e096-11eb-8e84-e053a162fd44.jpg)

# Sputnik prices bot
Change config in config.yaml
1. api_key
2. change url in image_path
3. Change home_text, help_text, tokens and tokens_sites (if necessary).

## Install
1. Copy to project directory
2. Run command:
composer install
3. Add to cron files:
3.1. cronbot.php
3.2. osmosis_cron.php
4. Add webhook to Telegram bot api with url:
https://api.telegram.org/botAPI_KEY/setWebhook?url=URL_TO_telegrambot.php
With changeing API_KEY to bot api key and URL_TO_ to your path to the file telegrambot.php.
5. That is all.

For the config file, the component is used https://symfony.com/doc/current/components/yaml.html
