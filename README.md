-- SUMMARY --

The USAJobs module provides a block that displays all job openings for a
specific federal, state, or local agency. The data source for this module is
the USAJOBS.gov API, which includes all current openings posted on USAJOBS.gov.

To learn more about the module, visit the project page at: [https://www.drupal.org/project/usajobs](https://www.drupal.org/project/usajobs)

If you encounter any bugs or have feature suggestions, please submit them on the project's issue tracker at: [https://www.drupal.org/project/issues/usajobs](https://www.drupal.org/project/issues/usajobs)

-- REQUIREMENTS --

There are no specific requirements for this module.

-- INSTALLATION --

To install the USAJobs module, follow these steps:

- Visit [https://www.drupal.org/project/usajobs](https://www.drupal.org/project/usajobs) for further information on installation.

-- CONFIGURATION --

To configure the USAJobs module, follow these steps:

- Go to Configuration -> USAJobs (admin/config/services/usajobs)
- In the Basic settings section, enter your User-Agent and Authorization Key.
- In the Query Parameters section, select the desired Organization.

-- CUSTOM TEMPLATE --

To override the default USAJobs template, follow these steps:

1. Copy the file `usajobs-item.html.twig` to your theme folder.
2. Clear the caches.
