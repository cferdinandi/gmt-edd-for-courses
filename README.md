# GMT EDD for Courses
Add Easy Digital Downloads integration to the [GMT Courses plugin](https://github.com/cferdinandi/gmt-courses).

[Download EDD for Courses](https://github.com/cferdinandi/gmt-edd-for-courses/archive/master.zip)



## Getting Started

Getting started with EDD for Courses is as simple as installing a plugin:

1. Upload the `gmt-edd-for-courses` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Add the store URL, your EDD API public key and token under `Lessons` > `EDD Options`.
4. Select which downloads should have access to a course under `Lessons` > `Courses` in the Dashboard.

And that's it, you're done. Nice work!

It's recommended that you also install the [GitHub Updater plugin](https://github.com/afragen/github-updater) to get automattic updates.



## Utility methods

```php
/**
 * Get data from the EDD API
 * @param  string $type The type of data to get from the EDD API
 * @param  array  $args Any API arguments you want to add to the EDD API query
 * @return array        The downloads
 */
 function gmt_edd_for_courses_get_from_api( $type = 'products', $args = array() )


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


## How to Contribute

Please read the [Contribution Guidelines](CONTRIBUTING.md).



## License

The code is available under the [GPLv3](LICENSE.md).