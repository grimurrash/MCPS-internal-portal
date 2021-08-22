<!doctype html>
<html lang="ru" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>DakBoard</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,300,400">
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.15.2/css/all.css"
          integrity="sha384-yJpxAFV0Ip/w63YkZfDWDTU6re/Oc3ZiVqMa97pi8uPt92y0wzeK3UFM2yQRhEom"
          crossorigin="anonymous">
    <link rel="stylesheet" href="{{asset('css/board.css')}}">
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}">
</head>
<body class="font-large">
<header class="header">
    <img class="header_photo" alt="test" src="https://patriotsport.moscow/wp-content/uploads/2020/04/artboard-3.svg?1626259592"/>
    <div class="block block-datetime">
        <span class="block-datetime_time" id="datetime_time"></span>
        <span class="block-datetime_date" id="datetime_date"></span>
    </div>
</header>
<main>
    <div class="block-person-info">
        <div class="block has-background block-person-info_fio">
            <div class="block-body">
                <strong>{{ $fio }}</strong>
                <br>
                <em>{{ $post }}</em>
            </div>
        </div>
        <div class="block has-background block-person-info_office">
            <div class="block-title">Кабинет</div>
            <div class="block-body">{{ $office }}</div>
        </div>
    </div>
    <div class="row">
        <div class="col col-6">
            <div class="block weather block-weather text-center has-background block-font-medium "
                 id="block-weather">
                <div class="block-name">Погода</div>
                <div class="block-body">
                    <div class="weather-item weather-current">
                        <div>
                            <span id="current-temp"></span><sup>°</sup>
                        </div>
                        <img src=""
                             class="weather-current-icon" id="current-icon" alt="">
                    </div>

                    <div class="weather-forecast-group">
                        <div class="text-center weather-item weather-forecast weather-forecast-1">
                            <div class="day" id="weather-forecast-1_day"></div>
                            <img src="" alt="" id="weather-forecast-1_icon">
                            <br>
                            <div class="weather-forecast-item">
                                <span class="weather-forecast-item-high"
                                      id="weather-forecast-1_high"></span><sup>°</sup>
                                <span class="weather-forecast-item-low" id="weather-forecast-1_low"></span><sup>°</sup>
                            </div>

                        </div>
                        <div class="text-center weather-item weather-forecast weather-forecast-2">
                            <div class="day" id="weather-forecast-2_day"></div>
                            <img src="" alt="" id="weather-forecast-2_icon">
                            <br>
                            <div class="weather-forecast-item">
                                <span class="weather-forecast-item-high"
                                      id="weather-forecast-2_high"></span><sup>°</sup>
                                <span class="weather-forecast-item-low" id="weather-forecast-2_low"></span><sup>°</sup>
                            </div>

                        </div>
                        <div class="text-center weather-item weather-forecast weather-forecast-3">
                            <div class="day" id="weather-forecast-3_day"></div>
                            <img src="" alt="" id="weather-forecast-3_icon">
                            <br>
                            <div class="weather-forecast-item">
                                <span class="weather-forecast-item-high"
                                      id="weather-forecast-3_high"></span><sup>°</sup>
                                <span class="weather-forecast-item-low" id="weather-forecast-3_low"></span><sup>°</sup>
                            </div>

                        </div>
                        <div class="text-center weather-item weather-forecast weather-forecast-4">
                            <div class="day" id="weather-forecast-4_day"></div>
                            <img src="" alt="" id="weather-forecast-4_icon">
                            <br>
                            <div class="weather-forecast-item">
                                <span class="weather-forecast-item-high"
                                      id="weather-forecast-4_high"></span><sup>°</sup>
                                <span class="weather-forecast-item-low" id="weather-forecast-4_low"></span><sup>°</sup>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="block-education-news block has-background">
                <div class="block-title">Новости образования - портал "Российское образование"</div>
                <div class="block-body">
                    <a href="#" target="_blank" class="news-title" id="education-news-title"></a>
                    <div class="news-date" id="education-news-date"></div>
                    <div class="news-description" id="education-news-description"></div>
                </div>
            </div>
            <div class="block-mcps-news block has-background">
                <div class="block-title">Новости - Московский Центр Патриот Спорт</div>
                <div class="block-body">
                    <a href="#" target="_blank" class="news-title" id="mcps-news-title"></a>
                    <div class="news-date" id="mcps-news-date"></div>
                    <div class="news-description" id="mcps-news-description"></div>
                </div>
            </div>
        </div>
        <div class="col col-6">
            <div class="block-calendar block has-background">
                <div class="calendar-container">
                    <div class="calendar-upload">Загрузка календаря...</div>
                </div>
            </div>
        </div>
    </div>
</main>
<footer>
    <div class="row">
        <div class="col col-6">
            <div class="block has-background block-travel-time">
                <div class="block-title">Bрeмя в пути дo:</div>
                <div class="travel-time-container">
                </div>
            </div>
        </div>
        <div class="col col-3">
            <div class="block has-background block-wi-fi">
                <div class="block-title">WI-FI</div>
                <img src="{{ asset('images/wifi.JPG') }}"
                     alt="wifi qr code" class="block-body">
            </div>
        </div>
        <div class="col col-3">
            <div class="block has-background block-request">
                <div class="block-title">Заявка в IТ</div>
                <img src="{{ asset('images/request.jpg') }}"
                     alt="request qr code" class="block-body">
            </div>
        </div>
    </div>
</footer>

<div class="block-gradient-top"></div>
<div class="block-gradient-bottom"></div>

<script>
  let boardId = {{ $id }};

  function f(x) {
    return (x * x - 90) * (x * x - 90) + 6
  }

  function result(x) {
    let a = 0,
        b = 0

    while (x > 0) {
      console.log(x)
      if (x % 2 > 0) {
        a += 1;
      } else {
        b += 1;
      }
      x = Math.floor(x / 2)
    }

    return `${a}, ${b}`
  }


  // update date time block
  function updateDateTime() {
    let now = new Date();

    document.getElementById('datetime_time').innerHTML = now.toLocaleString('ru', {
      hour: 'numeric',
      minute: 'numeric'
    })
    document.getElementById('datetime_date').innerHTML = now.toLocaleString('ru', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
    })

    setTimeout(updateDateTime, 5000)
  }

  updateDateTime();

  // update weather block
  let buildingLat = {{ $building['lat'] }},
      buildingLon = {{ $building['lon'] }};

  function updateWeather() {
    fetch(`https://portal.cpvs.moscow/api/board/yandexWeatherApi?lat=${buildingLat}&lan=${buildingLon}`)
        .then(res => res.json())
        .then(res => {
          let fact = res.fact
          document.getElementById('current-temp').innerHTML = fact.temp
          document.getElementById('current-icon').src = `https://yastatic.net/weather/i/icons/blueye/color/svg/${fact.icon}.svg`

          let forecasts = res.forecasts

          let day = forecasts[0].parts;
          document.getElementById('weather-forecast-1_day').innerHTML = 'Сегодня'
          document.getElementById('weather-forecast-1_icon').src = `https://yastatic.net/weather/i/icons/blueye/color/svg/${day.day_short.icon}.svg`
          document.getElementById('weather-forecast-1_high').innerHTML = day.day_short.temp
          document.getElementById('weather-forecast-1_low').innerHTML = day.day_short.temp_min < day.night.temp_min ? day.day_short.temp_min : day.night.temp_min;


          day = forecasts[1].parts;
          let week_day = new Date(forecasts[1].date)
          document.getElementById('weather-forecast-2_day').innerHTML = week_day.toLocaleString('ru', { weekday: 'short' })
          document.getElementById('weather-forecast-2_icon').src = `https://yastatic.net/weather/i/icons/blueye/color/svg/${day.day_short.icon}.svg`
          document.getElementById('weather-forecast-2_high').innerHTML = day.day_short.temp
          document.getElementById('weather-forecast-2_low').innerHTML = day.day_short.temp_min < day.night.temp_min ? day.day_short.temp_min : day.night.temp_min;

          day = forecasts[2].parts;
          week_day = new Date(forecasts[2].date)
          document.getElementById('weather-forecast-3_day').innerHTML = week_day.toLocaleString('ru', { weekday: 'short' })
          document.getElementById('weather-forecast-3_icon').src = `https://yastatic.net/weather/i/icons/blueye/color/svg/${day.day_short.icon}.svg`
          document.getElementById('weather-forecast-3_high').innerHTML = day.day_short.temp
          document.getElementById('weather-forecast-3_low').innerHTML = day.day_short.temp_min < day.night.temp_min ? day.day_short.temp_min : day.night.temp_min;

          day = forecasts[3].parts;
          week_day = new Date(forecasts[3].date)
          document.getElementById('weather-forecast-4_day').innerHTML = week_day.toLocaleString('ru', { weekday: 'short' })
          document.getElementById('weather-forecast-4_icon').src = `https://yastatic.net/weather/i/icons/blueye/color/svg/${day.day_short.icon}.svg`
          document.getElementById('weather-forecast-4_high').innerHTML = day.day_short.temp
          document.getElementById('weather-forecast-4_low').innerHTML = day.day_short.temp_min < day.night.temp_min ? day.day_short.temp_min : day.night.temp_min;
        })
    setTimeout(updateWeather, 600000)
  }

  updateWeather()

  function getDiffText(datetimeDiff) {
    let diffText = ''
    if (datetimeDiff.days > 0) {
      let days = datetimeDiff.days
      if (days === 1) diffText = '1 день назад'
      else if (days => 2 && days <= 4) diffText = days + ' дня назад'
      else diffText = days + ' дней назад'
    } else if (datetimeDiff.hour > 0) {
      let hour = datetimeDiff.hour
      if (hour === 1) diffText = '1 час назад'
      else if (hour => 2 && hour <= 4) diffText = hour + ' часа назад'
      else diffText = hour + ' часов назад'
    } else if (datetimeDiff.minute > 0) {
      let minute = datetimeDiff.minute
      if (minute === 1 || (minute > 20 && (minute % 10 === 1))) diffText = '1 минуту назад'
      else if ((minute => 2 && minute <= 4) || (minute > 20
          && ((minute % 10 === 2) || (minute % 10 === 3) || (minute % 10 === 4))))
        diffText = minute + ' минуты назад'
      else diffText = minute + ' минут назад'
    } else {
      diffText = 'только что'
    }
    return diffText
  }

  // update education news block
  let nextEducationNewsNumber = 0;
  const educationNewsUrl = 'https://www.edu.ru/news/glavnye-novosti/feed.rss'

  function updateEducationNews() {
    fetch(`https://portal.cpvs.moscow/api/board/getNews?number=${nextEducationNewsNumber}&newsUrl=${educationNewsUrl}`,)
        .then(res => res.json())
        .then(news => {
          nextEducationNewsNumber = news.nextNumber
          let diffText = getDiffText(news.dayDiff)
          document.querySelector('.block-education-news .block-body').classList.add('hide');
          setTimeout(function () {
            document.getElementById('education-news-title').href = news.link;
            document.getElementById('education-news-title').innerHTML = news.title;
            document.getElementById('education-news-title').title = news.title;
            document.getElementById('education-news-description').innerHTML = news.description;
            document.getElementById('education-news-date').innerHTML = diffText;
            document.querySelector('.block-education-news .block-body').classList.remove('hide');
            setTimeout(updateEducationNews, 20000);
          }, 1500)
        })
        .catch(res => {
          console.log(res)
        })
  }

  updateEducationNews()


  // update MCPS news
  let nextMCPSNewsNumber = 0;
  const MCPSNewsUrl = 'https://patriotsport.moscow/feed/?post_type=news'

  function updateMCPSNews() {
    fetch(`https://portal.cpvs.moscow/api/board/getNews?number=${nextMCPSNewsNumber}&newsUrl=${MCPSNewsUrl}`,)
        .then(res => res.json())
        .then(news => {
          nextMCPSNewsNumber = news.nextNumber
          let diffText = getDiffText(news.dayDiff)
          document.querySelector('.block-mcps-news .block-body').classList.add('hide');
          setTimeout(function () {
            document.getElementById('mcps-news-title').href = news.link;
            document.getElementById('mcps-news-title').innerHTML = news.title;
            document.getElementById('mcps-news-title').title = news.title;
            document.getElementById('mcps-news-description').innerHTML = news.description;
            document.getElementById('mcps-news-date').innerHTML = diffText;
            document.querySelector('.block-mcps-news .block-body').classList.remove('hide');
            setTimeout(updateMCPSNews, 20000);
          }, 1500)
        })
        .catch(res => {
          console.log(res)
        })
  }

  updateMCPSNews()

  // update Calendar
  function updateCalendar() {
    fetch(`https://portal.cpvs.moscow/api/board/getCalendar?boardId=${boardId}`)
        .then(res => res.json())
        .then(res => {
          document.querySelector('.block-calendar .calendar-container').classList.add('hide');

          setTimeout(function () {
            let container = '';
            let todayDate = new Date().getDate()
            Object.keys(res).map(dayKey => {
              let day = new Date(dayKey)
              let dayText = '';
              let dateDiff = day.getDate() - todayDate;
              if (dateDiff === 0)
                dayText = 'Сегодня'
              else if (dateDiff === 1) {
                dayText = 'Завтра'
              } else {
                dayText = day.toLocaleDateString('ru', { weekday: 'long' })
              }
              container +=
                  `<div class="day">
                      ${dayText}, <span>${day.toLocaleDateString('ru', { month: 'long', day: 'numeric' })}</span>
                   </div>
                   <ul class="calendar-items">`

              res[dayKey].forEach(item => {
                container += `
                    <li class="calendar-item">
                        <div class="calendar-item_header">
                            <div class="calendar-item_time">${item.startTime} — ${item.endTime}</div>
                            <div class="calendar-item-office">
                                <div class="calendar-item-office_label">место нахождения</div>
                                ${item.office !== '' ? item.office : '-'}
                            </div>
                        </div>
                        <div class="calendar-item_title">${item.title}</div>
                    </li>
                    `
              })
            })
            container += '</ul>'
            document.querySelector('.block-calendar .calendar-container').innerHTML = container
            document.querySelector('.block-calendar .calendar-container').classList.remove('hide');
            setTimeout(updateCalendar, 15 * 60000);
          }, 1500)
        })
  }

  updateCalendar()

  // update Building Distance
  function updateBuildingDistance() {
    fetch(`https://portal.cpvs.moscow/api/board/getDistance?boardId=${boardId}`)
        .then(res => res.json())
        .then(res => {
          setTimeout(function () {
            let container = '';
            res.forEach(item => {
               container +=  `
                    <div class="travel-time-item">
                        <span class="travel-name">${item.name}</span><br/>
                        <span class="travel-distance">${item.distance}</span> | <span class="travel-duration">${item.duration}</span>
                    </div>
               `
            })
            document.querySelector('.block-travel-time .travel-time-container').innerHTML = container
            setTimeout(updateBuildingDistance, 30 * 60000);
          }, 1500)
        })
  }

  updateBuildingDistance()

</script>

</body>
</html>
