-- SUMMARY --

The USAJobs module provides a block to display all opening jobs for a specific
federal, state or local agency. Data source comes from USAJOBS.gov API
which is includes all current openings posted on USAJOBS.gov.

For a full description of the module, visit the project page:
  https://www.drupal.org/project/usajobs

To submit bug reports and feature suggestions, or to track changes:
  https://www.drupal.org/project/issues/usajobs


-- REQUIREMENTS --

None.


-- INSTALLATION --

* Install as usual, see https://www.drupal.org/project/usajobs for further information.


-- CONFIGURATION --
* Go to Configuration -> USAJobs (admin/config/services/usajobs)
  - Basic setting: enter your User-Agent and Authorization Key.
  - Query Parameters: select Organization.

* Place USAJobs Listing block to designated page in Administration >> Structure >> Blocks layout:

-- CUSTOM TEMPLATE --
* Override USAJobs template
 - Copy file usajobs-item.html.twig to your theme folder.
 - Clear caches.
