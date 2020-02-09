# Export xls mixing data form a html table and a xls with PHP.

Simple code to convert html file to csv reading te html code , getting table values and moving them to an array for finally export the csv file.

This is a code in PHP with a form which uploads the html and export csv file.

We have to modify php.ini settings with the following parameters:

* file_uploads=On
* upload_max_filesize=100M
* memory_limit=512M
* max_execution_time=180
* output_buffering=On

#Directions 

Go to the follow web page , select a date and submit the information , save the web page report as html or htm and also click the export button on this report page to get an xls file. Later try on this web form to upload both of the files for the same day report and you can make a filtered search text with the added text input. This information will give you back a report only with filtered row concidences in a new xls file.

http://sentencias.tfjfa.gob.mx:8082/SICSEJL/faces/content/public/BoletinJurisdiccional.xhtml?fbclid=IwAR1-DULB5RE23oFs-up02AtwVzPUS9mFHbo8x39y9iYKZiZXnUAkBn4FalQ
  

