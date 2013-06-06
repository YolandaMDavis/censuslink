Beta
=====
Example call to the censuslink API

/beta/censuslink.php?city=13&county=121&action=getIncomeByCounty

getIncomeByCounty
getEducationByCounty
getEthnicityRaceByCounty


Actions and their specifications

Use actions to define what data is needed to retrieve
Ex. censuslink.php?action=getIncomeByCounty

Action : Parameters
Description

--------------------------------------------

getIncomeByCounty : county, state 
Use this action to get the number of households that fall into certain income levels by using county and state.
Defaults: county=121 (Fulton County) & state=13 (Georgia)


--------------------------------------------

getEducationByCounty : county, state
Use this action to get the number of households and their education level by using county and state.
Defaults: county=121 (Fulton County) & state=13 (Georgia)

--------------------------------------------

