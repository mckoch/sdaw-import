# Felder Gemeindekennziffern und Gemeinde formatieren: führende Null einfügen

update GEMEINDE_ZUORDNUNG set 
Gemeindekennziffer = concat('0', Gemeindekennziffer)
where convert(Gemeindekennziffer,unsigned) < 10000000;

update GEMEINDE_ZUORDNUNG set 
Kreis = concat('0', Kreis)
where convert(Kreis,unsigned) < 10000;


# besser: where length(Gemeindekennzoffer) <8;
