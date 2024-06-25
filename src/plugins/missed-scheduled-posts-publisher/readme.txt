=== Missed Scheduled Posts Publisher by WPBeginner ===

Stable tag:        2.0.0
Requires at least: 5.0
Tested up to:      6.3.1
Requires PHP:      5.6
License:           GPLv2
Tags:              Scheduled posts, Missed schedule, Cron
Contributors:      WPbeginner, smub, jaredatch, peterwilsoncc, tommcfarlin

Are your scheduled posts missing their publication times? Missed Scheduled Posts Publisher effectively resolves the 'missed scheduled post' error, ensuring that your content is reliably published on time, all while maintaining top-notch website performance.

== Description ==

= WordPress Missed Scheduled Post Publisher by WPBeginner =

Do you encounter the "missed scheduled post" error with your scheduled content?

Our plugin specializes in resolving the "missed scheduled post" issue with precision. It ensures that your scheduled posts consistently meet their publication deadlines. This post scheduler plugin has been meticulously crafted for optimal performance, guaranteeing zero impact on your website's speed and responsiveness.

= What Causes the Missed Scheduled Post Error? =

Not seeing WordPress scheduled posts show up on time?

WordPress relies on something called "WordPress cron jobs" to make these posts go live as scheduled. Think of "cron" as a fancy way of saying "commands that follow a schedule," just like your WordPress scheduled posts.

In the technical world, a real "cron job" operates at the server level. But since WordPress can't access that level directly, it does the next best thing by running a "simulated cron."

These simulated cron jobs, including the one for your scheduled posts, are supposed to kick in whenever someone (or even a bot) visits your site. But because it's not the real deal, there are times when it slips up and leads to a "missed schedule" hiccup.

= How Do I Fix the Missed Posts Schedule Error in WordPress =

Scheduled Post Check: Our post scheduler plugin diligently examines your posts every fifteen minutes to detect any missed schedule errors. If it finds one, it takes the initiative to automatically publish the missed post for you.

Comprehensive Checks: We employ a range of techniques to ensure that no scheduled post slips through the cracks, guaranteeing a seamless posting experience for you.

= Credits =

Missed Scheduled Posts Publisher is created by the <a href="https://www.wpbeginner.com/" rel="friend">WPBeginner</a> team.

= What's Next? =

To learn more about WordPress, you can visit <a href="https://www.wpbeginner.com/" rel="friend">WPBeginner</a> for tutorials on topics like:

* <a href="http://www.wpbeginner.com/wordpress-performance-speed/" rel="friend" title="Ultimate Guide to WordPress Speed and Performance">WordPress Speed and Performance</a>
* <a href="http://www.wpbeginner.com/wordpress-security/" rel="friend" title="Ultimate WordPress Security Guide">WordPress Security</a>
* <a href="http://www.wpbeginner.com/wordpress-seo/" rel="friend" title="Ultimate WordPress SEO Guide for Beginners">WordPress SEO</a>

...and many more <a href="http://www.wpbeginner.com/category/wp-tutorials/" rel="friend" title="WordPress Tutorials">WordPress tutorials</a>.

If you like our Missed Scheduled Posts Publisher plugin, then consider checking out our other projects:

* <a href="https://optinmonster.com/" rel="friend">OptinMonster</a> – Get More Email Subscribers with the most popular conversion optimization plugin for WordPress.
* <a href="https://wpforms.com/" rel="friend">WPForms</a> – #1 drag & drop online form builder for WordPress (trusted by 4 million sites).
* <a href="https://www.monsterinsights.com/" rel="friend">MonsterInsights</a> – See the Stats that Matter and Grow Your Business with Confidence. Best Google Analytics Plugin for WordPress.
* <a href="https://www.seedprod.com/" rel="friend">SeedProd</a> – Create beautiful landing pages with our powerful drag & drop landing page builder.
* <a href="https://wpmailsmtp.com/" rel="friend">WP Mail SMTP</a> – Improve email deliverability for your contact form with the most popular SMTP plugin for WordPress.
* <a href="https://rafflepress.com/" rel="friend">RafflePress</a> – Best WordPress giveaway and contest plugin to grow traffic and social followers.
* <a href="https://www.smashballoon.com/" rel="friend">Smash Balloon</a> – #1 social feeds plugin for WordPress - display social media content in WordPress without code.
* <a href="https://aioseo.com/" rel="friend">AIOSEO</a> – the original WordPress SEO plugin to help you rank higher in search results (trusted by over 2 million sites).
* <a href="https://www.pushengage.com/" rel="friend">PushEngage</a> – Connect with visitors after they leave your website with the leading web push notification plugin.
* <a href="https://trustpulse.com/" rel="friend">TrustPulse</a> – Add real-time social proof notifications to boost your store conversions by up to 15%.

Visit <a href="http://www.wpbeginner.com/" rel="friend">WPBeginner</a> to learn from our <a href="http://www.wpbeginner.com/category/wp-tutorials/" rel="friend">WordPress Tutorials</a> and find out about other <a href="http://www.wpbeginner.com/category/plugins/" rel="friend">best WordPress plugins</a>.

== Installation ==

1. Install Missed Scheduled Posts Publisher by uploading the `missed-scheduled-posts-publisher` directory to the `/wp-content/plugins/` directory. (See instructions on <a href="https://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners/" rel="friend">how to install a WordPress plugin</a>.)
2. Activate Missed Scheduled Posts Publisher through the `Plugins` menu in WordPress.

[youtube https://www.youtube.com/watch?v=QXbrdVjWaME]

== Frequently Asked Questions ==

= My scheduled post was published late, why? =

To avoid impacting the performance of your WordPress site, the Missed Scheduled Posts Publisher plugin checks for scheduled posts once every fifteen minutes. This is important because faster sites tend to rank higher in search results and get more traffic and conversions.

= I've enabled this on my site, why can't I see it in the admin? =

Missed Scheduled Posts Publisher is a set-and-forget plugin. There are no settings, since your site's scheduled posts will automatically be checked when the plugin is installed and activated.

== Changelog ==

= 2.0.0 =

* Added: Autoload dependencies
* Added: `CHANGELOG.md`
* Added: DocBlocks
* Added Review CTA
* Updated: Composer files to work with Composer 2.0+
* Updated: `.gitignore` to allow autoloading
* Updated: "Tested up to..." constraints
* Updated: `readme.txt`

= 1.0.1 =

* Improvement: Add a new filter `wpb_missed_scheduled_posts_publisher_frequency` to allows developers to change the frequency of the plugin checks.
* Test: Add PHP unit tests for the `wpb_missed_scheduled_posts_publisher_frequency` filter.
* Docs: Add new documentation for the plugin release process.
* Miscs: Update the plugin package name and description in composer.json.


= 1.0.0 =

* Initial plugin release.
