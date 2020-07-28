# eatout-scraper
A simple tool to scrape the Eat Out to Help Out discount scheme
https://www.tax.service.gov.uk/eat-out-to-help-out/find-a-restaurant/results?postcode=SW1a+1aa

# Looking for the data?
Here it is: [List of businesses registered for Eat Out to Help Out (CSV)](places.csv?raw=true) (4.1Mb)

# Methodology
Using the postcode list at http://api.getthedata.com/postcode/ I built a list of postcodes to create a grid of searches across the British Isles.

This should cover most populated areas quite well, but on the offchance there's a postcode covering a very wide area we might find the odd place missing.

This data doesn't cover Northern Ireland, so that's a work in progress.

# Disclaimers

* Some businesses may be missing. GOV.UK is the authoritative source.
* Northern Ireland is not included (yet).
* This has not been sanctioned by HM Treasury. Purely personal project!
* Some chains aren't listed individually. [A separate list is available](https://www.tax.service.gov.uk/eat-out-to-help-out/find-a-restaurant/restaurant-chains)

Contributions and visualisations welcome...!

# Licence
As per the original site, the data here is licenced under OGLv3 https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/

Lat/Lng coordinates are from http://api.getthedata.com/postcode/

Contains OS data © Crown copyright and database right (2020)
Contains Royal Mail data © Royal Mail copyright and Database right (2020)
Contains National Statistics data © Crown copyright and database right (2020)

# And finally...
If they get the chance (it's a busy time), I hope GOV.UK release the dataset in full. Quite happy if this entire script is rendered redundant :)

# Coverage
![coverage map of UK](coverage.png?raw=true)
