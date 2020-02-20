A page is divided in three sections :

- Header : the same for each page, included form header.php. It contains the menu ;
- Content : this part is actually built by the index.php script from the page parameter ;
- Footer : the same for each page, included form footer.php.

All pages are displayed through the root index.php page. This page must
receive a GET parameter named 'page'. This parameter indicates which HTML
page build and. The page name requested is build with the content of each
PHP file (sorted by name) of the directory which have the same name as
the page.

If the page requested is missing, a 404 error is sended, but if the page
parameter is empty or missing, the 'about' page is displayed.

The flexbox CSS box model is used to always display the footer at the bottom,
build the horizontal menu and resize the screenshots without JavaScript.

The minimum PHP version required is 5.3.0.

The minimal browser versions are indicated on caniuse.com website [2].

This little website CMS is licensed under the terms of CeCILL-C license [1].

                                               -- 2017.09.15, S. Aulery

-----------------------------------------------------------------------

[1] https://cecill.info/licences/Licence_CeCILL-C_V1-en.html
[2] https://caniuse.com/#feat=flexbox

-----------------------------------------------------------------------