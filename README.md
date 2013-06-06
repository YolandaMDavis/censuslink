censuslink
==========

Middleware API to interact with American Community Survey Census Data

Using JSON


V1
==

Example call censuslink/v1/api.php?action=income&state=ga&county=fulton

Actions
--------
income - get the income levels and total relevant persons
education - get the education levels and total relevent persons
ethnicity - get the number of people for different ethnicities
search - get back possible state or county matches
countylist - get list of counties and id's for a state **Requires state parameter**
statelist - get list of states with id's 



Parameters
---------
county - the id for the county that data should be returned for (feature coming for using name)
state - the id for the state tht data should be returned for (feature coming for using name)



