<?php
    $timezones = [
        'Etc/Greenwich', 'America/Adak', 'America/Argentina/Buenos_Aires', 'America/Argentina/La_Rioja', 'America/Argentina/San_Luis', 'America/Atikokan', 'America/Belem', 'America/Boise', 'America/Caracas', 'America/Chihuahua', 'America/Cuiaba', 'America/Denver', 'America/El_Salvador', 'America/Godthab', 'America/Guatemala', 'America/Hermosillo', 'America/Indiana/Tell_City', 'America/Inuvik', 'America/Kentucky/Louisville', 'America/Lima', 'America/Managua', 'America/Mazatlan', 'America/Mexico_City', 'America/Montreal', 'America/Nome', 'America/Ojinaga', 'America/Port-au-Prince', 'America/Rainy_River', 'America/Rio_Branco', 'America/Santo_Domingo', 'America/St_Barthelemy', 'America/St_Vincent', 'America/Tijuana', 'America/Whitehorse', 'America/Anchorage', 'America/Argentina/Catamarca', 'America/Argentina/Mendoza', 'America/Argentina/Tucuman', 'America/Atka', 'America/Belize', 'America/Buenos_Aires', 'America/Catamarca', 'America/Coral_Harbour', 'America/Curacao', 'America/Detroit', 'America/Ensenada', 'America/Goose_Bay', 'America/Guayaquil', 'America/Indiana/Indianapolis', 'America/Indiana/Vevay', 'America/Iqaluit', 'America/Kentucky/Monticello', 'America/Los_Angeles', 'America/Manaus', 'America/Mendoza', 'America/Miquelon', 'America/Montserrat', 'America/Noronha', 'America/Panama', 'America/Port_of_Spain', 'America/Rankin_Inlet', 'America/Rosario', 'America/Sao_Paulo', 'America/St_Johns', 'America/Swift_Current', 'America/Toronto', 'America/Winnipeg', 'America/Anguilla', 'America/Argentina/ComodRivadavia', 'America/Argentina/Rio_Gallegos', 'America/Argentina/Ushuaia', 'America/Bahia', 'America/Blanc-Sablon', 'America/Cambridge_Bay', 'America/Cayenne', 'America/Cordoba', 'America/Danmarkshavn', 'America/Dominica', 'America/Fort_Wayne', 'America/Grand_Turk', 'America/Guyana', 'America/Indiana/Knox', 'America/Indiana/Vincennes', 'America/Jamaica', 'America/Knox_IN', 'America/Louisville', 'America/Marigot', 'America/Menominee', 'America/Moncton', 'America/Nassau', 'America/North_Dakota/Beulah', 'America/Pangnirtung', 'America/Porto_Acre', 'America/Recife', 'America/Santa_Isabel', 'America/Scoresbysund', 'America/St_Kitts', 'America/Tegucigalpa', 'America/Tortola', 'America/Yakutat', 'America/Antigua', 'America/Argentina/Cordoba', 'America/Argentina/Salta', 'America/Aruba', 'America/Bahia_Banderas', 'America/Boa_Vista', 'America/Campo_Grande', 'America/Cayman', 'America/Costa_Rica', 'America/Dawson', 'America/Edmonton', 'America/Fortaleza', 'America/Grenada', 'America/Halifax', 'America/Indiana/Marengo', 'America/Indiana/Winamac', 'America/Jujuy', 'America/Kralendijk', 'America/Lower_Princes', 'America/Martinique', 'America/Merida', 'America/Monterrey', 'America/New_York', 'America/North_Dakota/Center', 'America/Paramaribo', 'America/Porto_Velho', 'America/Regina', 'America/Santarem', 'America/Shiprock', 'America/St_Lucia', 'America/Thule', 'America/Vancouver', 'America/Yellowknife', 'America/Araguaina', 'America/Argentina/Jujuy', 'America/Argentina/San_Juan', 'America/Asuncion', 'America/Barbados', 'America/Bogota', 'America/Cancun', 'America/Chicago', 'America/Creston', 'America/Dawson_Creek', 'America/Eirunepe', 'America/Glace_Bay', 'America/Guadeloupe', 'America/Havana', 'America/Indiana/Petersburg', 'America/Indianapolis', 'America/Juneau', 'America/La_Paz', 'America/Maceio', 'America/Matamoros', 'America/Metlakatla', 'America/Montevideo', 'America/Nipigon', 'America/North_Dakota/New_Salem', 'America/Phoenix', 'America/Puerto_Rico', 'America/Resolute', 'America/Santiago', 'America/Sitka', 'America/St_Thomas', 'America/Thunder_Bay', 'America/Virgin', 'Europe/Amsterdam', 'Europe/Berlin', 'Europe/Chisinau', 'Europe/Helsinki', 'Europe/Kiev', 'Europe/Madrid', 'Europe/Moscow', 'Europe/Prague', 'Europe/Sarajevo', 'Europe/Tallinn', 'Europe/Vatican', 'Europe/Zagreb', 'Europe/Athens', 'Europe/Brussels', 'Europe/Dublin', 'Europe/Istanbul', 'Europe/Ljubljana', 'Europe/Mariehamn', 'Europe/Oslo', 'Europe/Rome', 'Europe/Skopje', 'Europe/Tiraspol', 'Europe/Vilnius', 'Europe/Zurich', 'Europe/Belfast', 'Europe/Bucharest', 'Europe/Gibraltar', 'Europe/Jersey', 'Europe/London', 'Europe/Minsk', 'Europe/Paris', 'Europe/Samara', 'Europe/Sofia', 'Europe/Uzhgorod', 'Europe/Volgograd', 'Europe/Belgrade', 'Europe/Budapest', 'Europe/Guernsey', 'Europe/Kaliningrad', 'Europe/Luxembourg', 'Europe/Monaco', 'Europe/Podgorica', 'Europe/San_Marino', 'Europe/Stockholm', 'Europe/Vaduz', 'Europe/Warsaw', 'Africa/Abidjan', 'Africa/Asmera', 'Africa/Blantyre', 'Africa/Ceuta', 'Africa/Douala', 'Africa/Johannesburg', 'Africa/Kinshasa', 'Africa/Lubumbashi', 'Africa/Mbabane', 'Africa/Niamey', 'Africa/Timbuktu', 'Africa/Accra', 'Africa/Bamako', 'Africa/Brazzaville', 'Africa/Conakry', 'Africa/El_Aaiun', 'Africa/Juba', 'Africa/Lagos', 'Africa/Lusaka', 'Africa/Mogadishu', 'Africa/Nouakchott', 'Africa/Tripoli', 'Africa/Addis_Ababa', 'Africa/Bangui', 'Africa/Bujumbura', 'Africa/Dakar', 'Africa/Freetown', 'Africa/Kampala', 'Africa/Libreville', 'Africa/Malabo', 'Africa/Monrovia', 'Africa/Ouagadougou', 'Africa/Tunis', 'Africa/Algiers', 'Africa/Banjul', 'Africa/Cairo', 'Africa/Dar_es_Salaam', 'Africa/Gaborone', 'Africa/Khartoum', 'Africa/Lome', 'Africa/Maputo', 'Africa/Nairobi', 'Africa/Porto-Novo', 'Africa/Windhoek', 'Africa/Asmara', 'Africa/Bissau', 'Africa/Casablanca', 'Africa/Djibouti', 'Africa/Harare', 'Africa/Kigali', 'Africa/Luanda', 'Africa/Maseru', 'Africa/Ndjamena', 'Africa/Sao_Tome', 'Atlantic/Azores', 'Atlantic/Faroe', 'Atlantic/St_Helena', 'Atlantic/Bermuda', 'Atlantic/Jan_Mayen', 'Atlantic/Stanley', 'Atlantic/Canary', 'Atlantic/Madeira', 'Atlantic/Cape_Verde', 'Atlantic/Reykjavik', 'Atlantic/Faeroe', 'Atlantic/South_Georgia', 'Asia/Aden', 'Asia/Aqtobe', 'Asia/Baku', 'Asia/Calcutta', 'Asia/Dacca', 'Asia/Dushanbe', 'Asia/Hong_Kong', 'Asia/Jayapura', 'Asia/Kashgar', 'Asia/Kuala_Lumpur', 'Asia/Magadan', 'Asia/Novokuznetsk', 'Asia/Pontianak', 'Asia/Riyadh', 'Asia/Shanghai', 'Asia/Tehran', 'Asia/Ujung_Pandang', 'Asia/Vladivostok', 'Asia/Almaty', 'Asia/Ashgabat', 'Asia/Bangkok', 'Asia/Choibalsan', 'Asia/Damascus', 'Asia/Gaza', 'Asia/Hovd', 'Asia/Jerusalem', 'Asia/Kathmandu', 'Asia/Kuching', 'Asia/Makassar', 'Asia/Novosibirsk', 'Asia/Pyongyang', 'Asia/Saigon', 'Asia/Singapore', 'Asia/Tel_Aviv', 'Asia/Ulaanbaatar', 'Asia/Yakutsk', 'Asia/Amman', 'Asia/Ashkhabad', 'Asia/Beirut', 'Asia/Chongqing', 'Asia/Dhaka', 'Asia/Harbin', 'Asia/Irkutsk', 'Asia/Kabul', 'Asia/Katmandu', 'Asia/Kuwait', 'Asia/Manila', 'Asia/Omsk', 'Asia/Qatar', 'Asia/Sakhalin', 'Asia/Taipei', 'Asia/Thimbu', 'Asia/Ulan_Bator', 'Asia/Yekaterinburg', 'Asia/Anadyr', 'Asia/Baghdad', 'Asia/Bishkek', 'Asia/Chungking', 'Asia/Dili', 'Asia/Hebron', 'Asia/Istanbul', 'Asia/Kamchatka', 'Asia/Kolkata', 'Asia/Macao', 'Asia/Muscat', 'Asia/Oral', 'Asia/Qyzylorda', 'Asia/Samarkand', 'Asia/Tashkent', 'Asia/Thimphu', 'Asia/Urumqi', 'Asia/Yerevan', 'Asia/Aqtau', 'Asia/Bahrain', 'Asia/Brunei', 'Asia/Colombo', 'Asia/Dubai', 'Asia/Ho_Chi_Minh', 'Asia/Jakarta', 'Asia/Karachi', 'Asia/Krasnoyarsk', 'Asia/Macau', 'Asia/Nicosia', 'Asia/Phnom_Penh', 'Asia/Rangoon', 'Asia/Seoul', 'Asia/Tbilisi', 'Asia/Tokyo', 'Asia/Vientiane', 'Australia/Canberra', 'Australia/LHI', 'Australia/NSW', 'Australia/Adelaide', 'Australia/Darwin', 'Australia/Lord_Howe', 'Australia/Queensland', 'Australia/West', 'Australia/Tasmania', 'Australia/Broken_Hill', 'Australia/Hobart', 'Australia/North', 'Australia/Sydney', 'Australia/Brisbane', 'Australia/Eucla', 'Australia/Melbourne', 'Australia/South', 'Australia/Yancowinna', 'Australia/ACT', 'Australia/Currie', 'Australia/Lindeman', 'Australia/Perth', 'Australia/Victoria', 'Pacific/Chuuk', 'Pacific/Fiji', 'Pacific/Guam', 'Pacific/Kwajalein', 'Pacific/Niue', 'Pacific/Pitcairn', 'Pacific/Saipan', 'Pacific/Truk', 'Pacific/Chatham', 'Pacific/Fakaofo', 'Pacific/Guadalcanal', 'Pacific/Kosrae', 'Pacific/Nauru', 'Pacific/Palau', 'Pacific/Rarotonga', 'Pacific/Tongatapu', 'Pacific/Easter', 'Pacific/Funafuti', 'Pacific/Honolulu', 'Pacific/Majuro', 'Pacific/Norfolk', 'Pacific/Pohnpei', 'Pacific/Samoa', 'Pacific/Wake', 'Pacific/Auckland', 'Pacific/Enderbury', 'Pacific/Gambier', 'Pacific/Kiritimati', 'Pacific/Midway', 'Pacific/Pago_Pago', 'Pacific/Port_Moresby', 'Pacific/Tarawa', 'Pacific/Apia', 'Pacific/Efate', 'Pacific/Galapagos', 'Pacific/Johnston', 'Pacific/Marquesas', 'Pacific/Noumea', 'Pacific/Ponape', 'Pacific/Tahiti', 'Pacific/Wallis', 'Pacific/Yap', 'Antarctica/Casey', 'Antarctica/McMurdo', 'Antarctica/Vostok', 'Antarctica/Davis', 'Antarctica/Palmer', 'Antarctica/DumontDUrville', 'Antarctica/Rothera', 'Antarctica/Macquarie', 'Antarctica/South_Pole', 'Antarctica/Mawson', 'Antarctica/Syowa', 'Arctic/Longyearbyen', 'Indian/Antananarivo', 'Indian/Kerguelen', 'Indian/Reunion', 'Indian/Cocos', 'Indian/Mauritius', 'Indian/Christmas', 'Indian/Maldives', 'Indian/Comoro', 'Indian/Mayotte', 'Indian/Chagos', 'Indian/Mahe'
    ];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta content="ie=edge" http-equiv="x-ua-compatible">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="TG Panel">
    <meta name="format-detection" content="telephone=no">
    <title>TG Panel</title>
    <link rel="icon" href="/assets/img/core-img/favicon.ico">
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/animate.css">
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="login-area">
    <style>
        td, th {
            padding: 10px;
        }
    </style>
    <div class="main-content- h-100vh">
      <div class="container h-100">
        <div class="row h-100 align-items-center justify-content-center">
          <div class="col-lg-6">
            <div class="middle-box">
              <div class="card-body">
                <div class="log-header-area card p-4 mb-4 text-center">
                  <h5>Установка базовых настроек</h5>
                </div>
                <div class="card">
                  <form class="card-body p-4" method="post" action="/installer/index.php?step=2">
                        <?= isset($message) ? '<p style="font-weight: bold; color:red">'.$message.'</p>' : ''; ?>
                    <div class="mb-4">
                      <label class="form-label">Часовой пояс сайта</label>
                      <select class="form-select" name="installer_config[TIMEZONE]">

                        <?php foreach ($timezones as $timezone): ?>
                          <?php $selected = $timezone == 'Europe/Moscow' ? 'selected' : '' ?>
                          
                          <option value="<?= $timezone ?>" <?= $selected ?>><?= $timezone ?></option>

                        <?php endforeach ?>

                      </select>
                    </div>
                    <label class="form-label mb-3">Создание аккаунта администратора</label>
                    <div class="mb-3">
                      <label class="form-label">Логин</label>
                      <input type="text" class="form-control" name="create_admin[login]" value="admin">
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Пароль</label>
                      <input type="text" class="form-control" name="create_admin[password]" value="<?= substr(md5(rand(1,1000000).rand(1,1000000)), 0, 32); ?>">
                    </div>
                    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                        <i class='bx bxs-error'></i>
                        <div class="mx-3">Не забудьте записать пароль</div>
                    </div>
                    <div class="form-group mb-3 d-flex justify-content-center">
                      <button type="submit" class="btn btn-primary btn-lg px-5">Далее</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>   
</body>
</html>
