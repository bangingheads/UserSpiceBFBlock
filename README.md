# UserSpice Brute Force Block

This plugin prevents Brute Force login attempts by using an increasing timeout on failed logins.

UserSpice can be downloaded from their [website](https://userspice.com/) or on [GitHub](https://github.com/mudmin/UserSpice5)

## Setting Up

1. Copy the bfblock plugin folder from inside the repo into /usersc/plugins/
2. Open UserSpice Admin Panel and install plugin.
3. Configure plugin with your choice of settings or use default settings.

## Plugin Configuration

In plugin configuration you can set the timeouts for the amount of times in a specified time period.

These are checked in the time period specified in the login frame interval.

You may also turn on a ban threshold to ban an IP address after so many failed login attempts.

Make sure if you have a proxy in front of your server to choose the correct header that your proxy is using or you will block your proxy's IP.

## Questions

Any issues? Feel free to open an issue on Github or make a Pull Request.

Need help? Add me on Discord: BangingHeads#0001.

Any help with UserSpice can be asked in their [Discord](https://discord.gg/j25FeHu)
