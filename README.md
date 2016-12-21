# GMT EDD for Courses
Add Easy Digital Downloads integration to the [GMT Courses plugin](https://github.com/cferdinandi/gmt-courses).

[Download EDD for Courses](https://github.com/cferdinandi/gmt-edd-for-courses/archive/master.zip)



## Getting Started

Getting started with EDD for Courses is as simple as installing a plugin:

1. Upload the `gmt-edd-for-courses` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Select which downloads should have access to a course under `Lessons` > `Courses` in the Dashboard.

And that's it, you're done. Nice work!

It's recommended that you also install the [GitHub Updater plugin](https://github.com/afragen/github-updater) to get automattic updates.



## Utility methods

```php
/**
 * Get all available downloads
 * @param  boolean $any If true, get all downloads, including unpublished
 * @return array        The downloads
 */
gmt_edd_for_courses_get_downloads( $any = true );


/**
 * Get all available courses
 * @param  boolean $any If true, get all courses, including unpublished
 * @return array        The courses
 */
function gmt_edd_for_courses_get_courses( $any = true );


/**
 * Get the downloads a user has purchased
 * @param  string $email The user's email address
 * @return array         The user's downloads
 */
gmt_edd_for_courses_get_user_downloads( $email = null );


/**
 * Get the courses that the user has access to
 * @param  string $email The user's email address
 * @return array         The courses that the user has access to
 */
gmt_edd_for_courses_get_purchased_courses( $email = null );


/**
 * Check if user can access a course
 * @param  number $course_id The course ID
 * @param  string $email     The user's email address
 * @return boolean           If true, the user can access the course
 */
gmt_edd_for_courses_user_has_access( $course_id = null, $email = null );


/**
 * Get the download links that the user has access to for a course
 * @param  number $course_id The course ID
 * @param  string $email     The user's email address
 * @return array             The course downloads
 */
gmt_edd_for_courses_get_download_links( $course_id = null, $email = null );
```


## Shortcodes

### Dynamic "Buy Now" Links

Create buy now links that disable and display a custom message when the visitor has already purchased the download.

```html
[edd_for_courses_buy_now id="DOWNLOAD_ID"]
```

**Options:**

- `checkout` - If `true`, send buyer directly to checkout
- `gateway` - If `true`, send buyer directly to payment gateway
- `price` - (integer) A specific price ID to use for variable pricing
- `discount` - Automatically add a discount code to the purchase
- `buy` - The language to use for the link
- `owned` - The language to use if the visitor already owns the download
- `class` - The class(es) to use for the link

```html
[edd_for_courses_buy_now id="123" checkout="true" price="2" buy="Buy Now!" owned="You already own this course" class="btn"]
```



## How to Contribute

Please read the [Contribution Guidelines](CONTRIBUTING.md).



## License

The code is available under the [GPLv3](LICENSE.md).