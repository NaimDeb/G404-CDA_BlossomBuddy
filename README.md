# BlossomBuddy

API permettant de chercher des plantes, de les ajouter a sa liste et d'avoir les données météorologiques permettant de les arroser.

## Installation 

Nous utilisons 2 APIs, [Perenual](https://perenual.com/) et [WeatherAPI](https://www.weatherapi.com/my/)

Dans un fichier .env :

```
PLANT_API_KEY="MA_CLE_API_PERENUAL"
WEATHER_API_KEY="MA_CLE_API_WEATHERAPI"
```

Configurer Mailtrap

```
MAIL_MAILER=smtp

MAIL_HOST=
MAIL_PORT=2525

MAIL_USERNAME=

MAIL_PASSWORD=
```



```
npm run dev
```

Aller sur http://localhost:8000/api/documentation pour accéder à la documentation Swagger