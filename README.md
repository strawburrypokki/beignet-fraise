# BeignetFraise, your friendly twitch bot

## Starting the bot

The twitch account `BeignetFraise` is privately own by me.  
If you want to run this bot under another twitch account, you'll need to create said accound, and generate the Twitch OAuth key [here](https://twitchapps.com/tmi/) for the bot to authenticate with Twitch IRC.

Once done, configure the following environment variables (they are also available inside the `.env` file):

* `TWITCH_BOT_ACCOUNT` Case sensitive name of the twitch account. Used to react to viewers pinging the bot (`@BotName`)
* `TWITCH_OAUTH` The OAuth token used for authentication

Then run the following command to run the bot:
```php
bin/console twitch [twitch channel]
```

When the bot successfuly joins the channel, it will send a greetings message (this can be disabled).

## Development

You can start the bot by running the following command

```bash
TWITCH_CHANNEL=[channel] docker-compose up -d
```

It is also possible to monitor the bot activity through the docker logs

```bash
docker-compose logs -f app
```

## Banword configs

The bot can to automatically flag viewers' messages based on banwords configurations.

A banword configuration is a coma-separated list of keyword.  
If all keyword are detected in a message, the message is flagged.

Consider the following config `thanos,villain`.  
The message `Thanos is the villain of the story` will be flagged; because it contains both `thanos` and `villain`.  
The message `Thanos is the good guy` will not be flagged; because it contains `thanos`, but is missing `villain`.

Banword configuration can be a single word, if you need to be super restrictive on a particuliar topic.

### Behind the scene

The bot tries to detect the keyword within a message by using the PHP implementation of [Metaphone](https://www.php.net/manual/en/function.metaphone.php).  
This why way, configured banwords will also trigger modifications of said word

`demon`, `d e m o n`, `d3m0n` will all be flag as a variant of `demon`.


## Ping the bot

Simply ping the bot with `@BeignetFraise` to get a random answer. This is can be used to ensure the bot is currently connected to the channel

## Commands

### !banword

`!banword` commands can only be used by moderators and the broadcaster of the channel.

#### add

Add a new banword config.

Usage

```bash
!banword add [config name] [config value]
```

`[config name]` is the name of the config. It is used later to delete a single config.

`[config value]` is coma-separated list of keyword consisting of the banword config.  
With a multiple keyword config, all keywords must be in the message in order to be flagged.

#### delete

Delete an existing banword config.

Usage

```bash
!banword delete [config name]
```

`[config name]` is the name of the banword config to delete.

Example

`!banword delete marvelspoiler`

#### clear

Delete all existing banword configs.

Usage

```bash
!banword clear
```

#### list

List all existing banword configs.

Usage

```bash
!banword list
```

Example

```
!banword list

BeignetFraise: marvelspoiler: "thanos,villain" || dcspoiler: "batman,outfit,black"
```