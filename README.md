# eatout-scraper
A simple tool to scrape the Eat Out to Help Out discount scheme
https://www.tax.service.gov.uk/eat-out-to-help-out/find-a-restaurant/results?postcode=SW1a+1aa

# Methodology
I don't have any clean (licence-compatible) datasets of postcodes to hand, so I've used a strategy of starting at an obvious central location, pulling out postcodes from results and reusing those to make future searches.

This will work fine in urban areas, but will certainly miss entries in less-dense areas and possibly entire areas. We need to find a better way of doing this, otherwise it's not a comprehensive list.

Contributions and visualisations welcome...!

# Licence
As per the original site, the data here is licenced under OGLv3 https://www.nationalarchives.gov.uk/doc/open-government-licence/version/3/

Lat/Lng coordinates are from http://api.getthedata.com/postcode/

Contains OS data © Crown copyright and database right (2020)
Contains Royal Mail data © Royal Mail copyright and Database right (2020)
Contains National Statistics data © Crown copyright and database right (2020)


If they get the chance, I hope GOV.UK release the dataset in full. Quite happy if this entire script is rendered redundant :)

