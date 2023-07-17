FORMAT: 1A
HOST: http://test.api.mycrm.kz/

# API Mycrm

MyCRM API description

## IOS Appication [/v2/app/ios]

### Retrieve IOS Application info [GET]

+ Response 200 (application/json)

    + Attributes

        + name: `MyCRM` (string)
        + version: `0.1` (string)
        + update_url: `itms://itunes.apple.com/us/app/apple-store/id1332260619?mt=8` (string)

## ANDROID Appication [/v2/app/android]

### Retrieve ANDROID Application info [GET]

+ Response 200 (application/json)

    + Attributes

        + name: `MyCRM` (string)
        + version: `0.1` (string)
        + update_url: `application update url` (string)

## User [/v2/user?access-token={token}]

### View [GET]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (User)



## User Auth [/v2/user/login]

### Login [POST]

+ Request (application/json)

    + Attributes

        + username: `+7 701 381 71 15` (string, required)
        + password: `password` (string, required)

+ Response 200 (application/json)

    + Attributes

        + token: `string_access_token` (string)


## User Auth logout [/v2/user/logout?access-token={token}]

### Logout [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

+ Response 200 (application/json)




## Forgot password [/v2/user/forgot-password]

### Get code by sms [POST]

+ Request (application/json)

    + Attributes

        + username: `+7 701 381 71 15` (string, required)

+ Response 200 (application/json)




## Change password [/v2/user/change-password]

### Modify password [POST]

+ Request (application/json)

    + Attributes

        + username: `+7 701 381 71 15` (string, required)
        + code: `sms_code` (string, required)
        + password: `new password` (string, required)

+ Response 200 (application/json)

    + Attributes

        + token: `string_access_token` (string)



## User Company [/v2/user/company?access-token={token}&{expand}]

### Retrieve a User Company [GET]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - category,cashes,positions,divisions

+ Response 200 (application/json)

    + Attributes (Company)

### Update a User Company [PUT]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - category,cashes,positions,divisions

+ Request (application/json)

    + Attributes

        + name: `Company name` (string, required)
        + head_name: `Company head name` (string, required)
        + head_surname: `Company head surname` (string)
        + head_patronymic: `Company head patronymic` (string)
        + widget_prefix: `template` (string, required)
        + iik: `kz iik` (string)
        + bank: `HALYK BANK` (string)
        + bin: `bin number` (string)
        + bik: `bik code` (string)
        + license_issued: `2017-07-12` (string)
        + license_number: `17012586` (string)
        + address: `Company address` (string)
        + phone: `+7 701 381 71 55` (string)
        + online_start: `12:00` (string)
        + online_finish: `23:00` (string)
        + cashback_percent: 10 (number)

+ Response 200 (application/json)

    + Attributes (Company)

## User Company Balance [/v2/user/company/balance?access-token={token}]

### View [GET]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes

        + tariff: 0 (number)
        + balance: 9896  (number)
        + sms_limit: 1237  (number)
        + last_payment: "2017-11-20" (date)
        + next_payment: "2017-12-20" (date)

## User Push Key [/v2/user/push/key?access-token={token}]

### Save [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

       + Attributes

           + key: "YOUR_DEVICE_KEY" (string, required)

+ Response 200 (application/json)

       + Attributes

           + key: "YOUR_DEVICE_KEY" (string)

## User Push Test [/v2/user/push/test?access-token={token}]

### Send test notification to user [POST]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)


## User Permissions [/v2/user/permission?access-token={token}]

### List sidebar permissions [GET]

+ Parameters

    + token (required, string) - Access token


+ Response 200 (application/json)

    + Body

            {
                "timetable": [
                    "timetable"
                ],
                "customers": [
                    "customer",
                    "customerCategory",
                    "customerLoyalty",
                    "customerLost",
                    "customerSubscription"
                ],
                "orders": [
                    "order",
                    "staffReview",
                    "divisionReview",
                    "customerRequest"
                ],
                "finance": [
                    "cash",
                    "contractor",
                    "costItem",
                    "scheme",
                    "salaryPay",
                    "cashflow",
                    "salaryReport",
                    "reportPeriod",
                    "reportStaff",
                    "reportBalance",
                    "reportReferrer",
                    "cashback"
                ],
                "statistic": [
                    "statistic",
                    "statisticStaff",
                    "statisticService",
                    "statisticCustomer",
                    "statisticInsurance"
                ],
                "services": [
                    "divisionService"
                ],
                "warehouse": [
                    "warehouse"
                ],
                "settings": [
                    "staff",
                    "schedule",
                    "position",
                    "smsTemplates",
                    "documentTemplate",
                    "payment",
                    "webcall",
                    "insuranceCompany",
                    "teethDiagnosis"
                ]
            }


## Company Referrer Collection [/v2/company/referrer?access-token={token}&{pagination,name}]

### List all Company Referrers [GET]

+ Parameters

    + token (required, string) - Access token
    + name (optional, string) - Filter by name
    + pagination (optional, integer) - Toggle pagination (default '20' | disable '0')

+ Response 200 (application/json)

    + Attributes (array[Referrer])

### Create Referrer [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

    + Attributes

        + name: "Акбергенов Нурлан" (string, required)

+ Response 200 (application/json)

    + Attributes (Referrer)



## Company Cash Collection [/v2/company/cash?access-token={token}&{pagination}&{name}&{division_id}]

### List all Company Cashes [GET]

+ Parameters

    + token (required, string) - Access token
    + name (optional, string) - Filter by name
    + division_id (optional, integer) - Division ID
    + pagination (optional, integer) - Toggle pagination (default '20' | disable '0')

+ Response 200 (application/json)

    + Attributes (array[Cash])

## Company Cash [/v2/company/cash/{id}?access-token={token}]

### Retrieve a Company Cash [GET]

+ Parameters

    + id (required, number) - Company Cash ID
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (Cash)


## User Division Collection [/v2/user/division?access-token={token}&{expand}]

### List all Divisions [GET]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - phones,staffs,payments,self-staff,settings,company

+ Response 200 (application/json)

    + Attributes (array[Division])

### Create Division [POST]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - phones,staffs,payments,self-staff,settings,company

+ Request (application/json)

    + Attributes

        + address: `пр. Абая, 150` (string, required)
        + category_id: 2 (number, required)
        + city_id: 1 (number, required)
        + latitude: 43.2397314985 (number, required)
        + longitude: 76.9431758934 (number, required)
        + name: `Салон красоты "MYCRM"` (string, required)
        + working_finish: `20:00:00` (string, required)
        + working_start: `10:00:00` (string, required)
        + payments: [1, 2, 3] (array, required)
        + default_notification_time: 1 (number, required)
        + description: `` (string)
        + default_notification_time: 0 (number)
        + status: 1 (number)
        + url: `https://google.com` (string)
        + phones: [`+7 701 381 71 55`, `+7 701 381 71 56`]
        + notification_time_before_lunch: `19:00` (string)
        + notification_time_after_lunch: `12:00` (string)

+ Response 200 (application/json)

    + Attributes (Division)

## User Division [/v2/user/division/{id}?access-token={token}&{expand}]

### Retrieve a Division [GET]

+ Parameters

    + id (required, number) - Numeric id of the Division to perform action with.
    + token (required, string) - Access token
    + expand (optional, string) - phones,staffs,payments,self-staff,settings,company

+ Response 200 (application/json)

    + Attributes (Division)

### Update a Division [PUT]

+ Parameters

    + id (required, number) - Division id.
    + token (required, string) - Access token
    + expand (optional, string) - phones,staffs,payments,self-staff,settings,company

+ Request (application/json)

    + Attributes

        + address: `пр. Абая, 150` (string, required)
        + category_id: 2 (number, required)
        + city_id: 1 (number, required)
        + latitude: 43.2397314985 (number, required)
        + longitude: 76.9431758934 (number, required)
        + name: `Салон красоты "MYCRM"` (string, required)
        + working_finish: `20:00:00` (string, required)
        + working_start: `10:00:00` (string, required)
        + default_notification_time: 1 (number, required)
        + payments: [1, 2, 3] (array, required)
        + description: `` (string)
        + status: 1 (number)
        + url: `https://google.com` (string)
        + phones: [`+7 701 381 71 55`, `+7 701 381 71 56`]
        + default_notification_time: 0 (number)
        + notification_time_before_lunch: `19:00` (string)
        + notification_time_after_lunch: `12:00` (string)

+ Response 200 (application/json)

    + Attributes (Division)

## Company Insurance Collection [/v2/insurance-company?access-token={token}&{is_enabled,name,page,pagination}]

### List all Company Insurances [GET]

+ Parameters

    + token (required, string) - Access token
    + is_enabled (optional, boolean, `1||0`) - Filter available insurance companies. 0=false, 1=true
    + name (optional, string) - Filter bu name
    + page (optional, string) - Page number
    + pagination (optional, integer, `20`) - Toggle pagination (default '20' | disable '0')

+ Response 200 (application/json)

    + Attributes (array[InsuranceCompany])

## Company Insurance [/v2/insurance-company?access-token={token}]

### Update a Company Insurance [PUT]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

        {
            "companies": [1, 2, 3]
        }

+ Response 200 (application/json)

    + Body

            {
                "companies": [1, 2, 3]
            }

## User Staff Schedule [/v2/user/schedule?access-token={token}&{date}&{division_id}&{staff}&{expand}]

### Retrieve a Staff Schedule [GET]

+ Parameters

    + token (required, string) - Access token
    + date (required, string, `2017-01-01`) - Date filter
    + division_id (required, integer) - Division filter
    + staff (optional, integer|array) - Staff filter
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany,cash

+ Response 200 (application/json)

    + Attributes (array[UserSchedule])

## User Staff Schedule Resource [/v2/user/schedule?access-token={token}&{date}&{division_id}&{staff_id}&{expand}]

### Create a Staff Schedule [POST]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - division,staff

+ Request (application/json)

        {
            "staff_id": 749,
            "division_id": 49,
            "date": "2018-02-19",
            "start": "09:00",
            "end": "22:00",
            "break_start": "14:00",
            "break_end": "15:00"
        }

+ Response 200 (application/json)

    + Body

                {
                    "start_at": "2018-02-19 09:00:00",
                    "end_at": "2018-02-19 22:00:00",
                    "break_start": "2018-02-19 14:00",
                    "break_end": "2018-02-19 15:00",
                    "staff_id": 749,
                    "division_id": 49,
                    "id": 103650
                }

### Update a Staff Schedule [PUT]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - division,staff

+ Request (application/json)

        {
            "staff_id": 749,
            "division_id": 49,
            "date": "2018-02-19",
            "start": "07:00",
            "end": "23:00",
            "break_start": "12:00",
            "break_end": "14:00"
        }

+ Response 200 (application/json)

    + Body

                {
                    "id": 103650,
                    "staff_id": 749,
                    "start_at": "2018-02-19 07:00:00",
                    "end_at": "2018-02-19 23:00:00",
                    "division_id": 49,
                    "break_start": "2018-02-19 12:00",
                    "break_end": "2018-02-19 14:00"
                }

### Delete a Staff Schedule [DELETE]

+ Parameters

    + token (required, string) - Access token
    + staff_id (required, string) - Staff ID
    + division_id (required, string) - Division ID
    + date (required, string) - Date
    + expand (optional, string) - division,staff

+ Request (application/json)

        {
            "staff_id": 749,
            "division_id": 49,
            "date": "2018-02-19"
        }

+ Response 204 (application/json)

## Staff Schedule Template [/v2/staff/{staff_id}/schedule/template?access-token={token}&{expand}]

### Retrieve a Staff Schedule Template [GET]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - division,intervals,staff

+ Response 200 (application/json)

    + Body

                [
                    {
                        "id": 2,
                        "staff_id": 1533,
                        "division_id": 49,
                        "interval_type": 3,
                        "type": 1,
                        "created_at": "2018-02-20 04:53:34",
                        "updated_at": "2018-02-20 08:23:57",
                        "created_by": 8,
                        "updated_by": 8,
                        "intervals": [
                            {
                                "schedule_template_id": 2,
                                "day": 1,
                                "start": "10:00:00",
                                "end": "22:00:00",
                                "break_start": "14:00:00",
                                "break_end": "15:00:00"
                            },
                            {
                                "schedule_template_id": 2,
                                "day": 2,
                                "start": "09:00:00",
                                "end": "22:00:00",
                                "break_start": null,
                                "break_end": null
                            },
                            {
                                "schedule_template_id": 2,
                                "day": 3,
                                "start": "08:00:00",
                                "end": "22:00:00",
                                "break_start": null,
                                "break_end": null
                            },
                            {
                                "schedule_template_id": 2,
                                "day": 4,
                                "start": "08:00:00",
                                "end": "22:00:00",
                                "break_start": null,
                                "break_end": null
                            },
                            {
                                "schedule_template_id": 2,
                                "day": 5,
                                "start": "08:00:00",
                                "end": "22:00:00",
                                "break_start": null,
                                "break_end": null
                            }
                        ]
                    }
                ]

### Generate Staff Schedule With Template [POST]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - division,intervals,staff

+ Request (application/json)

        {
            "division_id" 49,
            "start": "2018-02-20",
            "interval_type": 3,
            "type": 1,
            "intervals": [
                1: {
                    "start": "10:00:00",
                    "end": "22:00:00",
                    "break_start": "14:00:00",
                    "break_end": "15:00:00"
                },
                2: {
                    "start": "09:00:00",
                    "end": "22:00:00",
                    "break_start": null,
                    "break_end": null
                },
                3: {
                    "start": "08:00:00",
                    "end": "22:00:00",
                    "break_start": null,
                    "break_end": null
                },
                4: {
                    "day": 4,
                    "start": "08:00:00",
                    "end": "22:00:00",
                    "break_start": null,
                    "break_end": null
                },
                5: {
                    "start": "08:00:00",
                    "end": "22:00:00",
                    "break_start": null,
                    "break_end": null
                }
            ]
        }

+ Response 200 (application/json)

    + Body

                {
                    "id": 2,
                    "staff_id": 1533,
                    "division_id": 49,
                    "interval_type": 3,
                    "type": 1,
                    "created_at": "2018-02-20 04:53:34",
                    "updated_at": "2018-02-20 08:23:57",
                    "created_by": 8,
                    "updated_by": 8,
                    "intervals": [
                        {
                            "schedule_template_id": 2,
                            "day": 1,
                            "start": "10:00:00",
                            "end": "22:00:00",
                            "break_start": "14:00:00",
                            "break_end": "15:00:00"
                        },
                        {
                            "schedule_template_id": 2,
                            "day": 2,
                            "start": "09:00:00",
                            "end": "22:00:00",
                            "break_start": null,
                            "break_end": null
                        },
                        {
                            "schedule_template_id": 2,
                            "day": 3,
                            "start": "08:00:00",
                            "end": "22:00:00",
                            "break_start": null,
                            "break_end": null
                        },
                        {
                            "schedule_template_id": 2,
                            "day": 4,
                            "start": "08:00:00",
                            "end": "22:00:00",
                            "break_start": null,
                            "break_end": null
                        },
                        {
                            "schedule_template_id": 2,
                            "day": 5,
                            "start": "08:00:00",
                            "end": "22:00:00",
                            "break_start": null,
                            "break_end": null
                        }
                    ]
                }


## User Staff Collection [/v2/user/staff?access-token={token}&{expand}&{division_id}]

### List all Staffs [GET]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - divisions,position,reviews,services,user_divisions,user_permissions
    + division_id (optional, string) - Filter by division id

+ Response 200 (application/json)

    + Attributes (array[Staff])

### Create a Staff [POST]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - divisions,position,reviews,services,user_divisions,user_permissions

+ Request (application/json)

        {
            "name": "Another name",
            "surname": "Another surname",
            "color": "color1",
            "phone": "+7 701 381 71 15",
            "company_position_ids": [{company_position_id}], // Positions the staff works in
            "gender": 1,
            "has_calendar": false,
            "description": "Some description",
            "description_private": "Private description",
            "division_ids": [{division_id}], // Divisions the staff works in

            "create_user": true,
            "user_permissions": ["timetableView", "companyCustomerOwner", "companyOwner", "orderOwner", "statisticView", "divisionServiceOwner", "cashOwner", "warehouseAdmin"],
            // List of system permissions staff has
            "user_divisions": [{division_id}], //
            "see_own_orders": true,
            "division_service_ids": [{division_service_id}]
        }

+ Response 200 (application/json)

    + Attributes (Staff)

## User Staff [/v2/user/staff/{id}?access-token={token}&{expand}]

### Retrieve a Staff [GET]

+ Parameters

    + id (required, number) - Numeric id of the Staff to perform action with.
    + token (required, string) - Access token
    + expand (optional, string) - divisions,position,reviews,services,user_divisions,user_permissions

+ Response 200 (application/json)

    + Attributes (Staff)

### Update a Staff [PUT]

+ Parameters

    + id (required, number) - Staff id.
    + token (required, string) - Access token
    + expand (optional, string) - divisions,position,reviews,services,user_divisions,user_permissions

+ Request (application/json)

        {
            "name": "Another name",
            "surname": "Another surname",
            "color": "color1",
            "phone": "+7 701 381 71 15",
            "company_position_ids": [{company_position_id}], // Positions the staff works in
            "gender": 1,
            "has_calendar": false,
            "description": "Some description",
            "description_private": "Private description",
            "division_ids": [{division_id}], // Divisions the staff works in

            "create_user": true,
            "user_permissions": ["timetableView", "companyCustomerOwner", "companyOwner", "orderOwner", "statisticView", "divisionServiceOwner", "cashOwner", "warehouseAdmin"],
            // List of system permissions staff has
            "user_divisions": [{division_id}], //
            "see_own_orders": true,
            "division_service_ids": [{division_service_id}]
        }

+ Response 200 (application/json)

    + Attributes (Staff)

## User Staff Services Addition [/v2/user/staff/{staff_id}/service/add?access-token={token}]

### Add Services To Staff [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

        [
            {division_service_id}
        ]

+ Response 200 (application/json)

    + Body

            [
                1,
                2
            ]

## User Staff Services Deletion [/v2/user/staff/{staff_id}/service/delete?access-token={token}]

### Delete Services From Staff [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

        [
            {division_service_id}
        ]

+ Response 200 (application/json)

    + Body

            [
                1,
                2
            ]


## SMS Template [/v2/user/sms-template?access-token={token}]

### List all templates [GET]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (array[SMSTemplate])

### Update all templates [PUT]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

            {
                {template_key}: {
                    "template": "Поздравляем Вас с Днём Рождения! Желаем всех благ и хорошего настроения! С Уважением, %COMPANY_NAME%",
                    "is_enabled": false,
                    "quantity": 5,
                    "quantity_type": 1
                }
            }

+ Response 200 (application/json)

    + Attributes (array[SMSTemplate])

## Company Position Collection [/v2/company/position?access-token={token}&{name}&{description}&{division_id}&{expand}]

### List all Company Positions [GET]

+ Parameters

    + token (required, string) - Access token
    + name (optional, string) - Filter by name
    + description (optional, string) - Filter by description
    + division_id (optional, integer) - Filter by division ID
    + expand (optional, string) - staffs, commentCategories, documentForms

+ Response 200 (application/json)

    + Attributes (array[Position])

### Create a Company Position [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

        {
            "name": "Another name",
            "description": "Some description",
            "categories": [{comment_category_id}],
            "documentForms": [{document_form_id}]
        }

+ Response 200 (application/json)

    + Attributes (Position)

## Company Position [/v2/company/position/{id}&access-token={token}&{expand}]

### Retrieve a Company Position [GET]

+ Parameters

    + id (required, number) - Company Position id
    + token (required, string) - Access token
    + expand (optional, string) - staffs, commentCategories, documentForms

+ Response 200 (application/json)

    + Attributes (Position)

### Update a Company Position [PUT]

+ Parameters

    + id (required, number) - Company position id.
    + token (required, string) - Access token

+ Request (application/json)

        {
            "name": "Some name",
            "description": "Some description",
            "categories": [{comment_category_id}],
            "documentForms": [{document_form_id}]
        }

+ Response 200 (application/json)

    + Attributes (Position)

## Company Payments Collection [/v2/company/payment?access-token={token}{value}&{code}&{description}&{message}&{start}&{end}]

### List all Company Payment actions [GET]

+ Parameters

    + token (required, string) - Access token
    + value (optional, integer) - Filter by value
    + code (optional, string) - Filter by code
    + description (optional, string) - Filter by description
    + message (optional, string) - Filter by message
    + start (optional, string) - Filter by start date
    + end (optional, string) - Filter by end date

+ Response 200 (application/json)

    + Attributes (array[PaymentAction])

## Company Payments Collection Export [/v2/company/payment/export?access-token={token}{value}&{code}&{description}&{message}&{start}&{end}]

### Export all Company Payment actions [GET]

+ Parameters

    + token (required, string) - Access token
    + value (optional, integer) - Filter by value
    + code (optional, string) - Filter by code
    + description (optional, string) - Filter by description
    + message (optional, string) - Filter by message
    + start (optional, string) - Filter by start date
    + end (optional, string) - Filter by end date

+ Response 200 (application/vnd.ms-excel)

## Country Collection [/v2/country{?expand,active,name,page,pagination}]

### List all Countries [GET]

+ Parameters

    + expand (optional, string) - cities
    + name (optional, string) - Filter by name
    + active (optional, boolean) - Filter by status
    + page (optional, string) - Page number
    + pagination (optional, integer) - Toggle pagination (default '20' | disable '0')

+ Response 200 (application/json)

    + Attributes (array[Country])

## Country [/v2/country/{id}{?expand}]

### Retrieve a Country [GET]

+ Parameters

    + id (required, number) - Retrieve the country by id
    + expand (optional, string) - cities

+ Response 200 (application/json)

    + Attributes (Country)


## City Collection [/v2/country/{country_id}/city{?page,country_id,name}]

### List all Countries [GET]

+ Parameters

    + page (optional, string) - Page number
    + country_id (required, number) - FIlter by company
    + name (required, string) - Filter by name

+ Response 200 (application/json)

    + Attributes (array[City])

## City [/v2/country/{country_id}/city/{id}]

### Retrieve a City [GET]

+ Parameters

    + country_id (required, number)
    + id (required, number)

+ Response 200 (application/json)

    + Attributes (City)



## Customer Collection  [/v2/customer?access-token={token}&{term,name,lastname,patronymic,phone,iin,id_card_number,pagination,sort,expand}]

### List all Customers [GET]

+ Parameters

    + token (required, string) - Access token
    + term (optional, number) - Search term
    + name (optional, string) - Customer name
    + lastname (optional, string) - Customer lastname
    + patronymic (optional, string) - Customer patronymic
    + phone (optional, string) - Customer phone
    + iin (optional, number) - Customer iin
    + id_card_number (optional, number) - Customer card number
    + categories (optional, array) - Categories
    + staff (optional, array) - Employers
    + services (optional, array) - Services
    + birthFrom (optional, string)
    + birthTo (optional, string)
    + paidMin (optional, integer) - Revenue
    + paidMax (optional, integer) 
    + visitCountMin (optional, integer) - number of visits
    + visitCountMax (optional, integer)
    + visitedFrom(optional, string)
    + visitedTo(optional, string)
    + firstVisitedFrom (optional, string)
    + firstVisitedTo (optional, string)
    + smsMode(optional, integer) - 0 OR 1,
    + smsFrom(optional, string)
    + smsTo (optional, string)
    + city (optional, string)
    + pagination (optional, number) - Pagination, default 20
    + sort (optional, string) - алфавит => name, время создания = id, максимум выручки = moneySpent, максимум скидки = discount
    + expand (optional, string) - customer, files, documents

+ Response 200 (application/json)

    + Attributes (array[Customer])

### Create a Customer [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

            {
                "name": "Another name",
                "lastname": "Another surname",
                "phone": "+7 701 381 71 15",
                "email": "tastembekov.anuar@mycrm.kz",
                "gender": 1,
                "birth_date": "1990-06-13",
                "address": "г. Кокшетау, Северная 35, кв. 302",
                "city": 72,
                "source_id": 1,
                "comments": "",
                "sms_birthday": true,
                "sms_exclude": false,
                "balance": 1000,
                "discount": 0,
                "patronymic": "Another patronymic",
                "iin": "900613350146",
                "id_card_number": "",
                "job": "Job name",
                "employer": "Employer name",
                "phones": [
                    ['value' => '+7 701 381 71 15'],
                    ['value' => '+7 701 381 71 51'],
                ],
                "insurance_company_id": {insurance_company_id},
                "insurance_expire_date": {expire_date|date|Y-m-d},
                "insurance_policy_number": {policy_number|string},
                "insurer": {insurer_name|string},
                "medical_record_id": {medical_record_id|string}
            }

+ Response 200 (application/json)

    + Attributes (Customer)


## Customer [/v2/customer/{id}?access-token={token}]

### Retrieve Customer [GET]

+ Parameters

    + token (required, string) - Access token
    + id (required, number) - Customer id

+ Response 200 (application/json)

    + Attributes (Customer)

### Update a Customer [PUT]

+ Parameters

    + id (required, number) - Customer id.
    + token (required, string) - Access token

+ Request (application/json)

            {
                "name": "Another name",
                "lastname": "Another surname",
                "phone": "+7 701 381 71 15",
                "email": "tastembekov.anuar@mycrm.kz",
                "gender": 1,
                "birth_date": "1990-06-13",
                "address": "г. Кокшетау, Северная 35, кв. 302",
                "city": 72,
                "source_id": 1,
                "comments": "",
                "sms_birthday": true,
                "sms_exclude": false,
                "balance": 1000,
                "discount": 0,
                "patronymic": "Another patronymic",
                "iin": "900613350146",
                "id_card_number": "",
                "job": "Job name",
                "employer": "Employer name",
                "insurance_company_id": {insurance_company_id},
                "insurance_expire_date": {expire_date|date|Y-m-d},
                "insurance_policy_number": {policy_number|string},
                "insurer": {insurer_name|string},
                "phones": [
                    ['value' => '+7 701 381 71 15'],
                    ['value' => '+7 701 381 71 51'],
                ],
                "medical_record_id": {medical_record_id|string}
            }

+ Response 200 (application/json)

    + Attributes (Customer)


## Customer Lost Collection  [/v2/customer/lost?access-token={token}&{term}&{division}&{number_of_days}&{name}&{lastname}&{patronymic}&{phone}&{iin}&{id_card_number}&{pagination}&{expand}]

### List lost Customers [GET]

+ Parameters

    + token (required, string) - Access token
    + term (optional, string) - Search term(name, surname, patronymic, iin, id_card_number, phone)
    + division (optional, number) - Filter by company division
    + number_of_days (optional, number) - Number of days clients didn't come, default 30
    + name (optional, string) - Customer name
    + lastname (optional, string) - Customer lastname
    + patronymic (optional, string) - Customer patronymic
    + phone (optional, string) - Customer phone
    + iin (optional, number) - Customer iin
    + id_card_number (optional, number) - Customer card number
    + pagination (optional, number) - Pagination, default 20
    + expand (optional, string) - customer, files, documents

+ Response 200 (application/json)

    + Attributes (array[Customer])


## Customer Export  [/v2/customer/export?access-token={token}]

### Export Customer to Excel File [POST]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/vnd.ms-excel)

## Customer Import  [/v2/customer/import?access-token={token}]

### Import Customer from Excel File [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (multipart/form-data; boundary=---BOUNDARY)

            ------BOUNDARY
            Content-Disposition: form-data; name="excelFile"; filename="Клиенты_01-02-2018-154222.xls"
            Content-Type: application/vnd.ms-excel


            ------BOUNDARY--

+ Response 200 (application/json)

    + Body

            {
                "message": "1 клиентов успешно загружены в систему"
            }



## Customer Delete Multiple  [/v2/customer/multiple/delete?access-token={token}]

### Delete Multiple Customers [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

            {
                "ids": [1, 2, 3]
            }

+ Response 200 (application/json)

    + Body

            {
                "message": "success"
            }



## Customer Add Categories Multiple  [/v2/customer/multiple/add-categories?access-token={token}]

### Add Categories To Multiple Customers [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

            {
                "ids": [1, 2, 3],
                "category_ids": [1, 2, 3]
            }

+ Response 200 (application/json)

    + Body

            {
                "message": "success"
            }


## Customer Send SMS Multiple  [/v2/customer/multiple/send-request?access-token={token}]

### Send Request To Multiple Customers [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

            {
                "ids": [1, 2, 3],
                "message": "Hello World!!!"
            }

+ Response 200 (application/json)

    + Body

            {
                "status": "success",
                "message": "SMS успешно отправлены"
            }


## Customer Source Collection [/v2/customer/source?access-token={token}&{name}&{pagination}]

### List all Customer Sources [GET]

+ Parameters

    + token (required, string) - Access token
    + name (optional, string) - Filter by name
    + pagination (optional, integer) - Pagination, default 20

+ Response 200 (application/json)

    + Attributes (array[CustomerSource])

### Create a Customer Source [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

            {
                "name": "Some name"
            }

+ Response 200 (application/json)

    + Attributes (CustomerSource)


## Customer Source [/v2/customer/source/{id}?access-token={token}]

### Retrieve a Customer Source [GET]

+ Parameters

    + id (required, number) - Numeric id of the Division to perform action with.
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (CustomerSource)


### Update a Customer Source [PUT]

+ Parameters

    + id (required, number) - Numeric id of the Division to perform action with.
    + token (required, string) - Access token

+ Request (application/json)

            {
                "name": "Some name"
            }

+ Response 200 (application/json)

    + Attributes (CustomerSource)


## Customer Source Move [/v2/customer/source/{id}/destination/{destination_id}?access-token={token}]

### Move customers to another source [PUT]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes

        + total_moved: 2 (number)

## Customer Loyalty Collection [/v2/customer/lotyalty?access-token={token}]

### List all Customer Loyalties [GET]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (array[CustomerLoyalty])


### Create a Customer Loyalty [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

            {
                "name": "Some name",
                "mode": 0,
                "event": 0,
                "discount": 42,
                "amount": 42000
            }

+ Response 200 (application/json)

    + Attributes (CustomerLoyalty)



## Customer Loyalty [/v2/customer/loyalty/{id}?access-token={token}]

### Retrieve a Customer Loyalty [GET]

+ Parameters

    + id (required, number) - Numeric id of the Division to perform action with.
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (CustomerLoyalty)


### Update a Customer Loyalty [PUT]

+ Parameters

    + id (required, number) - Numeric id of the Division to perform action with.
    + token (required, string) - Access token

+ Request (application/json)

            {
                "discount": 21,
                "amount": 21000,
            }

+ Response 200 (application/json)

    + Attributes (CustomerLoyalty)

## Customer Category Collection [/v2/customer/category?access-token={token}&{name}&{color}&{discount}]

### List all Customer Categories [GET]

+ Parameters

    + token (required, string) - Access token
    + name (optional, string) - Filter by name
    + color (optional, string) - Filter by color
    + discount (optional, integer) - Filter by discount

+ Response 200 (application/json)

    + Attributes (array[CustomerCategory])


### Create a Customer Category [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

            {
                "name": "Some name" (required),
                "discount": 42,
            }

+ Response 200 (application/json)

    + Attributes (array[CustomerCategory])



## Customer Category [/v2/customer/category/{id}?access-token={token}]

### Retrieve a Customer Category [GET]

+ Parameters

    + id (required, number) - Numeric id of the Division to perform action with.
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (CustomerCategory)



### Update a Customer Category [PUT]

+ Parameters

    + id (required, number) - Numeric id of the Division to perform action with.
    + token (required, string) - Access token

+ Request (application/json)

            {
                "name": "Some new name",
                "color": "#424242",
                "discount": 42,
            }

+ Response 200 (application/json)

    + Attributes (CustomerCategory)


## Customer History [/v2/customer/{id}/history?access-token={token}]

### Retrieve Customer History [POST]

+ Parameters

    + id (required, number) - Customer id
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Body

            [
                {
                    "created_at": "2018-02-19 10:02:21",
                    "action": "Создан",
                    "user": "Name Surname"
                },
                {
                    "created_at": "2018-02-21 09:50:36",
                    "action": "Изменен",
                    "user": "Name Surname",
                    "sms_exclude": true,
                    "city": "МойГород",
                    "updated_time": "2018-02-21 09:50:36",
                    "updated_user_id": 8
                }
            ]

## Order Collection [/v2/order?access-token={token}&{company_customer_id}&{staff_id,type,status,from,to,expand}]

### Retrieve an Order Collection [GET]

+ Parameters

    + status (optional, number) - Order status
    + company_customer_id (optional, number) - Customer id
    + type (optional, number) - Order type
    + staff_id (optional, number) - Staff id
    + from (optional, date, `yyyy-mm-dd`) - Filter list by date
    + to (optional, date, `yyyy-mm-dd`) - Filter list by date
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Response 200 (application/json)

    + Attributes (array[Order])

### Create an Order [POST]

+ Request (application/json)

    + Attributes (CreateOrderForm)

+ Response 200 (application/json)

    + Attributes (Order)

## Order [/v2/order/{id}?access-token={token}&{expand}]

### Retrieve Order [GET]

+ Parameters

    + id (required, number) - Order id
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Response 200 (application/json)

    + Attributes (Order)

### Update Order [PUT]

+ Parameters

    + id (required, number) - Order id
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Request (application/json)

        {
            "customer_name": "Гульнар",
            "customer_surname": "Ниязбекова",
            "customer_patronymic": "Ниязбековна",
            "customer_phone": "+7 701 222 44 30",
            "customer_source_name": "Some source name to create",
            "customer_source_id": 1,
            "company_cash_id": 255,
            "datetime": "2018-05-05 09:00:00",
            "division_id": 219,
            "hours_before": 0,
            "referrer_id": 23,
            "referrer_name": "new referrer name",
            "insurance_company_id": {insurance_company_id|null},
            "note": "напомнить пациентке о проф.осмотре, прошло пол года, позвонить и записать ее",
            "contacts": [{id,name,phone}],
            "payments": [{"payment_id": {payment_id}, "amount": {amount}}],
            "services": [{discount,division_service_id,duration,quantity,price}],
            "products": [
                {
                    quantity,
                    price,
                    product_id,
                },
            ],
            "staff_id": 1092,
            "categories": [{category_id],
            "customer_birth_date": "2000-04-20",
            "customer_gender": 2
        }

+ Response 200 (application/json)

    + Attributes (Order)


### Delete Order [DELETE]

+ Parameters

    + id (required, number) - Order id
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (Order)

## Order Export  [/v2/order/export?access-token={token}&{company_customer_id}&{staff_id}&{type}&{status}&{from}&{to}&{expand}]

### Export Orders to Excel File [POST]

+ Parameters

    + token (required, string) - Access token
    + company_customer_id (optional, number) - Customer id
    + type (optional, number) - Order type
    + staff_id (optional, number) - Staff id
    + from (optional, date, `yyyy-mm-dd`) - Filter list by date
    + to (optional, date, `yyyy-mm-dd`) - Filter list by date
    + status (optional, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Response 200 (application/vnd.ms-excel)

## Order Cancel [/v2/order/cancel/{id}?access-token={token}&{expand}]

### Cancel Order [POST]

+ Parameters

    + id (required, number) - Order id
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Response 200 (application/json)

    + Attributes (Order)


## Order Checkout [/v2/order/checkout/{id}?access-token={token}&{expand}]

### Checkout Order [POST]

+ Parameters

    + id (required, number) - Order id
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Request (application/json)

        {
            "customer_name": "Ниязбекова Гульнар",
            "customer_phone": "+7 701 222 44 30",
            "company_cash_id": 255,
            "datetime": "2018-05-05 09:00:00",
            "division_id": 219,
            "hours_before": 0,
            "note": "напомнить пациентке о проф.осмотре, прошло пол года, позвонить и записать ее",
            "contacts": [{id,name,phone}],
            "payments": [{"payment_id": {payment_id}, "amount": {amount}}],
            "services": [{discount,division_service_id,duration,quantity,price}],
            "products": [
                {
                    quantity,
                    price,
                    product_id,
                }
            ],
            "staff_id": 1092
        }

+ Response 200 (application/json)

    + Attributes (Order)


## Order Return [/v2/order/return/{id}?access-token={token}&{expand}]

### Return an Order [POST]

+ Parameters

    + id - Order id
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Response 200 (application/json)

    + Attributes (Order)

## Order Enable [/v2/order/enable/{id}?access-token={token}&{expand}]

### Enable an Order [POST]

+ Parameters

    + id - Order id
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Response 200 (application/json)

    + Attributes (Order)

## Order Update Duration [/v2/order/update-duration/{id}?access-token={token}&{expand}]

### Update Order duration [POST]

+ Parameters

    + id (required, number) - Order id
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Request (application/json)

        {
            "end": "2018-05-05 16:00:00"
        }

+ Response 200 (application/json)

    + Attributes (Order)


## Order Drag'N'Drop [/v2/order/drop/{id}?access-token={token}&{expand}]

### Move Order [POST]

+ Parameters

    + id (required, number) - Order id
    + token (required, string) - Access token
    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany

+ Request (application/json)

        {
            "start": "2018-05-05 09:00:00",
            "staff_id": 1092
        }

+ Response 200 (application/json)

    + Attributes (Order)


## Order History [/v2/order/{id}/history?access-token={token}]

### Retrieve Order History [POST]

+ Parameters

    + id (required, number) - Order id
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (array[OrderHistory])

## Order Overlapping [/v2/order/overlapping?access-token={token}]

### Check Existence OF Order

+ Parameters

    + token
    

+ Request (application/json)

        {
            "datetime": "2018-05-05 10:00",
            "division_id": 219,
            "staff_id": 1092
        }

+ Response 200 (application/json)

    + Body
   
        {
            overlapping: true
        }

## Document Dental Card Collection [/v2/document/tooth/{number}?access-token={token}&{pagination}&{document_id}&{diagnosis_id}&{mobility}&{company_customer_id}]

### List all Dental Card Elements [GET]

+ Parameters

    + token (required, string) - Access token
    + pagination (optional, integer, `20`) - Page size, set 0 to disable pagination
    + document_id (optional, integer)
    + number (optional, integer)
    + diagnosis_id (optional, integer)
    + mobility (optional, integer)
    + company_customer_id (optional, integer)

+ Response 200 (application/json)

    + Attributes (array[DentalCardElement])


## Order Document Template Collection [/v2/order/document-template?access-token={token}&{pagination}&{category_id}&{name}]

### List all Order Document Templates [GET]

+ Parameters

    + token (required, string) - Access token
    + pagination (optional, integer, `20`) - Page size, set 0 to disable pagination
    + category_id (optional, integer) - Filter by Service Categeory ID
    + name (optional, string) - Filter by Name

+ Response 200 (application/json)

    + Attributes (array[OrderDocumentTemplate])

## Order Document Template [/v2/order/document-template/{id}?access-token={token}]

### Retrieve a Order Document Template [GET]

+ Parameters

    + id (required, number) - Payment id
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (OrderDocumentTemplate)

## Order Files Collection [/v2/order/{order_id}/file?access-token={token}]

### Upload a File [POST]

+ Parameters

    + order_id (required, number) - Order id
    + token (required, string) - Access token

+ Request (application/json)

            {
                "file": some_file
            }

+ Response 200 (application/json)

    + Attributes (File)

## Order File [/v2/order/{order_id}/file/{id}?access-token={token}]

### Delete File [DELETE]

+ Parameters

    + id (required, number) - File id
    + order_id (required, number) - Order id
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes

        + message: `Успешно удалено` (string)

## Order Document Collection [/v2/order/{order_id}/document?access-token={token}&{name}&{date}&{template_id}]

### List all Order Documents [GET]

+ Parameters

    + order_id (required, integer) - Order ID
    + token (required, string) - Access token
    + name (optional, string) - Filter by Name
    + date (optional, date) - Filter by Date
    + template_id (optional, integer) - Filter by Order Document Template ID

+ Response 200 (application/json)

    + Attributes (array[OrderDocument])

### Generate an Order Document [POST]

+ Parameters

    + order_id (required, integer) - Order ID
    + token (required, string) - Access token
    + template_id (required, integer) - Order Document Template ID

+ Request (application/json)

            {
                "template_id": {template_id}
            }

+ Response 200 (application/json)

    + Attributes (OrderDocument)

## Order Document [/v2/order/{order_id}/document/{id}?access-token={token}]

### Retrieve a Order Document [GET]

+ Parameters

    + order_id (required, number) - Order ID
    + id (required, number) - Payment ID
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (OrderDocument)

## Pending Order Collection [/v2/pending-order?access-token={token}&{expand}]

### Retrieve an Pending Order Collection [GET]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - customer,staff

+ Response 200 (application/json)

    + Attributes (array[Order])

### Create an Pending Order [POST]

+ Request (application/json)

        {
            "customer_name": "Ниязбекова Гульнар",
            "customer_phone": "+7 701 222 44 30",
            "company_customer_id": 184494,
            "note": "напомнить пациентке о проф.осмотре, прошло пол года, позвонить и записать ее",
            "date": "2018-05-05",
            "division_id": 219,
            "staff_id": 1092
        }

+ Response 200 (application/json)

    + Attributes (Order)

## Pending Order [/v2/pending-order/{id}?access-token={token}&{expand}]

### Retrieve Pending Order [GET]

+ Parameters

    + id (required, number) - Pending Order id
    + token (required, string) - Access token
    + expand (optional, string) - customer,staff

+ Response 200 (application/json)

    + Attributes (Order)

### Update Pending Order [PUT]

+ Parameters

    + id (required, number) - Pending Order id
    + token (required, string) - Access token
    + expand (optional, string) - customer,staff

+ Request (application/json)

        {
            "customer_name": "Ниязбекова Гульнар",
            "customer_phone": "+7 701 222 44 30",
            "company_customer_id": 184494,
            "note": "напомнить пациентке о проф.осмотре, прошло пол года, позвонить и записать ее",
            "date": "2018-05-05",
            "division_id": 219,
            "staff_id": 1092
        }

+ Response 200 (application/json)

    + Body

            {
                "id": 117160,
                "className": null,
                "color": "color5",
                "company_customer_id": 184494,
                "company_cash_id": 255,
                "customer_name": "Ниязбекова Гульнар",
                "customer_phone": "+7 701 222 44 30",
                "datetime": "2018-05-05 00:00:00",
                "division_id": 219,
                "editable": true,
                "end": "2018-05-05 09:15:00",
                "hours_before": 0,
                "insurance_company_id": null,
                "note": "напомнить пациентке о проф.осмотре, прошло пол года, позвонить и записать ее",
                "products_discount": 0,
                "referrer_id": null,
                "resourceId": 1092,
                "staff_id": 1092,
                "start": "2018-05-05 09:00:00",
                "status": 5,
                "title": "Ниязбекова Гульнар\n+7 701 222 44 30\nповторный прием\n'напомнить пациентке о проф.осмотре, прошло пол года, позвонить и записать ее'\n"
            }

## Enable Pending Order [/v2/pending-order/enable/{id}?access-token={token}&{expand}]

### Set Pending Order regular Order [POST]

+ Parameters

    + id (required, number) - Pending Order id
    + token (required, string) - Access token
    + expand (optional, string) - customer,staff

+ Request (application/json)

        {
            "start": "2018-05-05 12:30:00",
            "staff_id": 1092
        }

+ Response 200 (application/json)

    + Attributes (Order)

## Payment Collection [/v2/payment]

### List all Payments [GET]

+ Response 200 (application/json)

    + Attributes (array[Payment])

## Payment [/v2/payment/{id}]

### Retrieve a Payment [GET]

+ Parameters

    + id (required, number) - Payment id

+ Response 200 (application/json)

    + Attributes (Payment)


## Root Service Category Collection [/v2/service/root-category{?expand,name}]

### List all Root Categories [GET]

+ Parameters

    + expand (optional, string) - subcategories
    + name (optional, string) - subcategories

+ Response 200 (application/json)

    + Attributes (array[ServiceCategory])

## Root Service Category [/v2/service/root-category/{id}{?expand}]

### Retrieve a Root Service Category [GET]

+ Parameters

    + id (required, number) - Root Categeory id
    + expand (optional, string) - subcategories

+ Response 200 (application/json)

    + Attributes (ServiceCategory)

## Service Category Collection [/v2/service/category/access-token={token}&{expand,name}]

### List all Service Categories [GET]

+ Parameters

    + token
    + expand (optional, string) - subcategories
    + name (optional, string)

+ Response 200 (application/json)

    + Attributes (array[ServiceCategory])

### Create Service Category [POST]

+ Parameters

    + token
    + expand (optional, string) - subcategories

+ Request (application/json)

        {
            "name": "New Category",
            "parent_category_id": 1
        }

+ Response 200 (application/json)

    + Attributes (ServiceCategory)

## Service Category [/v2/service/category/{id}?access-token={token}&{expand}]

### Get Service Category [GET]

+ Parameters

    + id
    + token
    + expand (optional, string) - subcategories

+ Response 200 (application/json)

    + Attributes (ServiceCategory)

### Update Service Category [PUT]

+ Parameters

    + id
    + token
    + expand (optional, string) - subcategories

+ Request (application/json)

        {
            "name": "Протезирование",
            "parent_category_id": 14
        }

+ Response 200 (application/json)

    + Attributes (ServiceCategory)

## Delete Service Categroy [DELETE]

+ Response 204 (application/json)

## Staff Schedule Collection [/v2/division/{division_id}/staff/{staff_id}/schedule?access-token={token}&{start_time}&{finish_time}]

### List all Schedules [GET]

+ Parameters

    + division_id (required, integer) - Staff division ID
    + staff_id (required, integer) - Staff ID
    + token (required, string) - Access token
    + start_time (required, string, `2017-01-01 00:00:00`) - Date filter
    + finish_time (required, string, `2017-01-02 00:00:00`) - Date filter

+ Response 200 (application/json)

    + Attributes (array[StaffSchedule])

## Services Collection [/v2/division/service?access-token={token}&{expand}]

### List all Services [GET]

+ Parameters

    + token (required, string) - Access token
    + expand (optional, string) - products, insurance-companies

+ Response 200 (application/json)

    + Attributes (array[Service])


## Service [/v2/division/service/{id}{?expand}]

### Retrieve service [GET]

+ Parameters

    + id (required, number) - Root Categeory id
    + expand (optional, string) - products, insurance-companies

+ Response 200 (application/json)

    + Attributes (Service)

## Division Payment Collection [/v2/division/{id}/payment?access-token={token}]

### List all Division Payments [GET]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (array[Payment])


## Categories Collection Of Staff [/v2/division/{division_id}/staff/{staff_id}/categories?access-token={token}&{expand}]

### List all Categories [GET]

+ Parameters

    + token (required, string) - Access token
    + division_id (required, integer) - Division ID
    + staff_id (required, integer) - Staff ID
    + expand (optional, string) - services

+ Response 200 (application/json)

    + Attributes (array[ServiceCategory])


## Medical Document Form Collection [/v2/document/form?access-token={token}&{expand}]

### Retrieve a Document Form Collection [GET]

+ Parameters

    + expand (optional, string) - elements

+ Response 200 (application/json)

    + Attributes (array[MedicalDocumentForm])


## Medical Document Form [/v2/document/form/{id}?access-token={token},{expand}]

### Retrieve a Document Form [GET]

+ Parameters

    + id (required, number)
    + expand (optional, string) - elements

+ Response 200 (application/json)

    + Attributes (MedicalDocumentForm)



## Medical Document Collection [/v2/document?access-token={token}&{document_form_id}&{customer_id}&{expand}]

### Retrieve a Document Collection [GET]

+ Parameters

    + expand (optional, string) - documentForm,customer,dentalCard,manager,services,staff,values
    + customer_id (optional, integer) - Filter by Customer ID
    + document_form_id (optional, integer) - Filter by Docuemnt Form ID

+ Response 200 (application/json)

    + Attributes (array[MedicalDocument])


## Medical Document [/v2/document/{id}?access-token={token},{expand}]

### Retrieve a Document [GET]

+ Parameters

    + id (required, number)
    + expand (optional, string) - documentForm,customer,dentalCard,manager,services,staff,values

+ Response 200 (application/json)

    + Attributes (MedicalDocument)


### Create a Document [POST]

+ Parameters

    + id (required, number) - Document Form ID
    + expand (optional, string) - documentForm,customer,dentalCard,manager,services,staff,values

+ Request (application/json)

            {
                "customer_id": 1,
                "treatment_plan": "Some text",
                "dentalCard": [
                    {
                        "number": 47,
                        "diagnosis_id": 10,
                        "mobility": 2
                    }
                ],
                "services": [
                    {
                        "service_id": 1,
                        "price": 500,
                        "discount": 0,
                        "quantity": 1
                    }
                ]
            }

+ Response 200 (application/json)

    + Body

            {
                "id": 1,
                "customer_id": 1,
                "dentalCard": [
                    {
                        "number": 47,
                        "diagnosis_id": 10,
                        "mobility": 2,
                        "diagnosis": {
                             "id": 10,
                             "company_id": 176,
                             "name": "корень",
                             "abbreviation": "R",
                             "color": "#CCC"
                        }
                    }
                ],
                "services": [
                    {
                        "document_id": 1,
                        "service_id": 1,
                        "price": 500,
                        "discount": 0,
                        "quantity": 1
                    }
                ]
                "values": [
                    {
                        "document_id": 1,
                        "document_form_element": 1,
                        "key": "treatment_plan",
                        "value": "Some text"
                    }
                ]
            }

### Update a Document [PUT]

+ Parameters

    + id (required, number) - Document ID
    + expand (optional, string) - documentForm,customer,dentalCard,manager,staff,values

+ Request (application/json)

        {
            "treatment_plan": "Edited text",
            "dentalCard": [
                {
                    "number": 47,
                    "diagnosis_id": 11,
                    "mobility": 2
                }
            ]
        }

+ Response 200 (application/json)

    + Body

            {
                "id": 1,
                "customer_id": 1,
                "dentalCard": [
                    {
                        "number": 47,
                        "diagnosis_id": 11,
                        "mobility": 2,
                        "diagnosis": {
                             "id": 11,
                             "company_id": 176,
                             "name": "корень",
                             "abbreviation": "R",
                             "color": "#CCC"
                        }
                    }
                ],
                "values": [
                    {
                        "document_id": 1,
                        "document_form_element": 1,
                        "key": "treatment_plan",
                        "value": "Edited text"
                    }
                ]
            }

## Medical Document File [/v2/document/generate/{id}?access-token={token}]

### Download a Document [GET]

+ Parameters

    + id (required, number) - Document ID

+ Response 200 (application/msword)

    + Body

            {"file"}

## Tooth Diagnoses Collection [/v2/tooth-diagnosis?access-token={token}&{name}&{abbreviation}&{color}&{pagination}]

### List all Tooth Diagnoses [GET]

+ Parameters

    + token (required, string) - Access token
    + name (optional, string) - Filter by name
    + abbreviation (optional, string) - Filter by abbreviation
    + color (optional, string) - Filter by color
    + pagination (optional, integer) - Pagination, default 20

+ Response 200 (application/json)

    + Attributes (array[ToothDiagnosis])

### Create Tooth Diagnosis [POST]

+ Parameters

    + token (required, string) - Access token

+ Request (application/json)

        {
            "name": "корень",
            "abbreviation": "R",
            "color": "#F00"
        }

+ Response 200 (application/json)

    + Attributes (ToothDiagnosis)

## Tooth Diagnosis [/v2/tooth-diagnosis/{id}?access-token={token}]

### Retrieve a Tooth Diagnosis [GET]

+ Parameters

    + id (required, number) - Numeric id of the Tooth Diagnosis to perform action with.
    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (ToothDiagnosis)

### Update a Tooth Diagnosis [PUT]

+ Parameters

    + id (required, number) - Numeric id of the Tooth Diagnosis to perform action with.
    + token (required, string) - Access token

+ Request (application/json)

        {
            "name": "корень",
            "abbreviation": "R",
            "color": "#F00"
        }

+ Response 200 (application/json)

    + Body

            {
                "id": 520,
                "company_id": 176,
                "name": "корень",
                "abbreviation": "R",
                "color": "#F00"
            }

### Delete a Tooth Diagnosis [DELETE]

+ Parameters

    + id (required, number) - Numeric id of the Tooth Diagnosis to perform action with.
    + token (required, string) - Access token

+ Response 204 (application/json)

+ Response 500 (application/json)

            {
                  "name": "Internal Server Error",
                  "message": "Не возможно удалить",
                  "code": 0,
                  "status": 500,
                  "type": "yii\\web\\ServerErrorHttpException"
            }



## Statistic General [/v2/statistic/?access-token={token}&{from}&{to}&{division}&{staff}&{user}]

### Retrieve Statistic [GET]

+ Parameters

    + from (optional, string) - Start date
    + to (optional, string) - End date
    + division (optional, number) - Division ID
    + staff (optional, number) - Staff ID
    + user (optional, number) - Company Customer ID

+ Response 200 (application/jsom)

    + Body

            {
                "income": 6400,
                "expense": 0,
                "profit": 6400,
                "averageRevenue": 3200,
                "occupancy": "7.69",
                "totalCount": 10,
                "disabledCount": 2,
                "finishedCount": 2,
                "enabledCount": 6,
                "revenues": [
                    {
                         "date": "2018-06-20",
                         "revenue": 664000
                    },
                    {
                        "date": "2018-06-21",
                        "revenue": 0
                    },
                    {
                        "date": "2018-06-22",
                        "revenue": 0
                    },
                    {
                        "date": "2018-06-23",
                        "revenue": 0
                    },
                    {
                        "date": "2018-06-24",
                        "revenue": 0
                    },
                    {
                        "date": "2018-06-25",
                        "revenue": 0
                    },
                    {
                        "date": "2018-06-26",
                        "revenue": 1200
                    }
``                ],
                "sources": [
                    {
                        "name": "Интернет",
                        "value": null
                    },
                    {
                        "name": "Реклама",
                        "value": null
                    },
                    {
                        "name": "Знакомые",
                        "value": null
                    },
                    {
                        "name": "Социальные сети",
                        "value": null
                    },
                    {
                        "name": "hjghjg",
                        "value": null
                    },
                    {
                        "name": "hfghfg",
                        "value": null
                    },
                    {
                        "name": "Неизвестно",
                        "value": 10
                    }
                ],
                "creators": [
                    {
                        "value": 2,
                        "name": "Дулат Махмутжанов "
                    }
                ],
                "types": [
                    {
                        "name": "Администратор",
                        "value": 43
                    },
                    {
                        "name": "Приложение",
                        "value": 3
                    },
                    {
                        "name": "Сайт",
                        "value": 0
                    }
                ]
            }

## Statistic Staff [/v2/statistic/staff?access-token={token}&{from}&{to}&{division}&{product_category_id}&{product_id}&{service_category_id}&{service_id}]

### Retrieve Statistic [GET]

+ Parameters

    + from (optional, string) - Start date
    + to (optional, string) - End date
    + division (optional, number) - Division ID
    + product_category_id (optional, number)
    + product_id (optional, number)
    + service_category_id (optional, number)
    + service_id (optional, number)

+ Response 200 (application/jsom)

    + Body

            [
                {
                    "id": 142,
                    "name": "Уилл",
                    "surname": "Смит",
                    "revenue": 0,
                    "position": {
                        "id": 540,
                        "name": "Хирург",
                        "description": null
                    },
                    "positions": [
                        {
                            "id": 540,
                            "name": "Хирург",
                            "description": null
                        }
                    ],
                    "ordersCount": 0,
                    "canceledOrdersCount": 0,
                    "productsCount": 0,
                    "servicesCount": 0,
                    "workedHours": "0.00",
                    "revenueShare": "0.0",
                    "_links": {
                        "self": {
                            "href": "http://api.mycrm.local/v2/staff/142"
                        }
                    }
                },
                {
                    "id": 748,
                    "name": "Абылай",
                    "surname": "Абилов",
                    "revenue": 6400,
                    "position": {
                        "id": 49,
                        "name": "Мужской мастер",
                        "description": ""
                    },
                    "positions": [
                        {
                            "id": 49,
                            "name": "Мужской мастер",
                            "description": ""
                        }
                    ],
                    "ordersCount": 45,
                    "canceledOrdersCount": 2,
                    "productsCount": 0,
                    "servicesCount": 2,
                    "workedHours": "2.00",
                    "revenueShare": "100.0",
                    "_links": {
                        "self": {
                            "href": "http://api.mycrm.local/v2/staff/748"
                        }
                    }
                }
            ]

## Statistic Staff Top [/v2/statistic/staff/top?access-token={token}&{from}&{to}&{division}&{product_category_id}&{product_id}&{service_category_id}&{service_id}]

### Retrieve Statistic Top [GET]

+ Parameters

    + from (optional, string) - Start date
    + to (optional, string) - End date
    + division (optional, number) - Division ID
    + product_category_id (optional, number)
    + product_id (optional, number)
    + service_category_id (optional, number)
    + service_id (optional, number)

+ Response 200 (application/jsom)

    + Body

            {
                "maxRevenue": {
                    "id": 748,
                    "name": "Абылай",
                    "surname": "Абилов",
                    "revenue": 6400,
                    "productsCount": 0,
                    "servicesCount": 2,
                    "position": {
                        "id": 49,
                        "name": "Мужской мастер",
                        "description": ""
                    },
                    "positions": [
                        {
                            "id": 49,
                            "name": "Мужской мастер",
                            "description": ""
                        }
                    ],
                    "ordersCount": 45,
                    "canceledOrdersCount": 2,
                    "workedHours": "2.00",
                    "revenueShare": "100.0",
                    "_links": {
                        "self": {
                            "href": "http://api.mycrm.local/v2/staff/748"
                        }
                    }
                },
                "minWorkedTime": {
                    "id": 142,
                    "name": "Уилл",
                    "surname": "Смит",
                    "revenue": 0,
                    "position": {
                        "id": 49,
                        "name": "Мужской мастер",
                        "description": ""
                    },
                    "positions": [
                        {
                            "id": 49,
                            "name": "Мужской мастер",
                            "description": ""
                        }
                    ],
                    "ordersCount": 0,
                    "canceledOrdersCount": 0,
                    "productsCount": 0,
                    "servicesCount": 0,
                    "workedHours": "0.00",
                    "revenueShare": "0.0",
                    "_links": {
                        "self": {
                            "href": "http://api.mycrm.local/v2/staff/142"
                        }
                    }
                },
                "maxWorkedTime": {
                    "id": 748,
                    "name": "Абылай",
                    "surname": "Абилов",
                    "revenue": 6400,
                    "position": {
                        "id": 49,
                        "name": "Мужской мастер",
                        "description": ""
                    },
                    "positions": [
                        {
                            "id": 49,
                            "name": "Мужской мастер",
                            "description": ""
                        }
                    ],
                    "ordersCount": 45,
                    "canceledOrdersCount": 2,
                    "productsCount": 0,
                    "servicesCount": 2,
                    "workedHours": "2.00",
                    "revenueShare": "100.0",
                    "_links": {
                        "self": {
                            "href": "http://api.mycrm.local/v2/staff/748"
                        }
                    }
                }
            }

## Statistic Service [/v2/statistic/service?access-token={token}&{to}&{from}&{category[]}&{division_service[]}&{division}]

### Retrieve Statistic [GET]

+ Parameters

    + from (optional, string) - Start date
    + to (optional, string) - End date
    + division (optional, number) - Division ID
    + `category` (optional, array[number]) - Service Category ID, can be multiple: category[]=42&category[]=43&category[]=44
    + `division_service` (optional, array[number]) - Division Service ID, can be multiple: division_service[]=42&division_service[]=43&division_service[]=44
    + sort (optional, string) - service_name, revenue, orders_count, average_cost

+ Response 200 (application/jsom)

    + Body

            [
                {
                    "id": 8784,
                    "name": "Лечение поверхностного кариеса\t",
                    "ordersCount": 1,
                    "revenue": 4800,
                    "revenueShare": "75.0"
                },
                {
                    "id": 8782,
                    "name": "Лечение пульпита трех корневого канала ",
                    "ordersCount": 1,
                    "revenue": 1600,
                    "revenueShare": "25.0"
                }
            ]
            
            
            
## Statistic Service Export[/v2/statistic/export-service?access-token={token}&{to}&{from}&{category[]}&{division_service[]}&{division}]

### Export Statistic [GET]

+ Parameters

    + from (optional, string) - Start date
    + to (optional, string) - End date
    + division (optional, number) - Division ID
    + `category` (optional, array[number]) - Service Category ID, can be multiple: category[]=42&category[]=43&category[]=44
    + `division_service` (optional, array[number]) - Division Service ID, can be multiple: division_service[]=42&division_service[]=43&division_service[]=44
    + sort (optional, string) - service_name, revenue, orders_count, average_cost

+ Response 200 (application/vnd.ms-excel)            

## Statistic Service Top [/v2/statistic/service/top?access-token={token}&{to}&{from}&{category}&{division}]

### Retrieve Statistic Top [GET]

+ Parameters

    + from (optional, string) - Start date
    + to (optional, string) - End date
    + division (optional, number) - Division ID
    + category (optional, number) - Service Category ID

+ Response 200 (application/jsom)

    + Body

            {
                "maxRevenue": {
                    "id": 8784,
                    "name": "Лечение поверхностного кариеса\t",
                    "ordersCount": 1,
                    "revenue": 4800
                },
                "mostPopular": {
                    "id": 8784,
                    "name": "Лечение поверхностного кариеса\t",
                    "ordersCount": 1,
                    "revenue": 4800
                },
                "leastPopular": {
                    "id": 8784,
                    "name": "Лечение поверхностного кариеса\t",
                    "ordersCount": 1,
                    "revenue": 4800
                }
            }

## Statistic Customer [/v2/statistic/customer?access-token={token}&{from}&{to}&{category}&{division}]

### Retrieve Statistic [GET]

+ Parameters

    + from (optional, string) - Start date
    + to (optional, string) - End date
    + division (optional, number) - Division ID
    + category (optional, number) - Customer Category ID
    + sort (optional, string) - customer_name, customer_phone, average_revenue, revenue, orders_count

+ Response 200 (application/jsom)

    + Body

            [
                {
                    "id": 640,
                    "fullName": "Anuar",
                    "phone": "+7 701 381 71 15",
                    "averageCheck": 3200,
                    "ordersCount": 2,
                    "revenue": 6400,
                    "revenueShare": "100.0"
                }
            ]

## Statistic Customer Top [/v2/statistic/customer/top?access-token={token}&{from}&{to}&{category}&{division}]

### Retrieve Statistic Top [GET]

+ Parameters

    + from (optional, string) - Start date
    + to (optional, string) - End date
    + division (optional, number) - Division ID
    + category (optional, number) - Service Category ID

+ Response 200 (application/jsom)

    + Body

            {
                "maxVisits": {
                    "id": 640,
                    "fullName": "Anuar",
                    "phone": "+7 701 381 71 15",
                    "averageCheck": 3200,
                    "ordersCount": 2,
                    "revenue": 6400
                },
                "maxRevenue": {
                    "id": 640,
                    "fullName": "Anuar",
                    "phone": "+7 701 381 71 15",
                    "averageCheck": 3200,
                    "ordersCount": 2,
                    "revenue": 6400
                },
                "maxDebt": null
            }

## Statistic Insurance [/v2/statistic/insurance?access-token={token}&{from}&{to}&{insurance_company_id}&{service_id}&{staff_id}]

### Retrieve Statistic [GET]

+ Parameters

    + token
    + from (optional, string) - Start date
    + to (optional, string) - End date
    + insurance_company_id (optional, number) - Division ID
    + service_id (optional, number) - Division Service ID
    + staff_id (optional, number) - Staff ID
    + sort (optional, string) - datetime, customer_name, customer_policy, insurance_company, staff_name, price

+ Response 200 (application/jsom)

    + Attributes (array[Order])

## Statistic WebCall [/v2/statistic/calls?access-token={token}&{from,to,action}]

### Retrieve Statistic [GET]

+ Parameters

    + token
    + from (optional, string) - Start date
    + to (optional, string) - End date

+ Response 200 (application/jsom)

    + Attributes
    
        + now: (array[WebCall])
        + previous: (array[WebCall])
    

## News Log [/v2/news-log?access-token={token}]

### Retrieve News Log [GET]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (array[News])

## Product Category Collection [/v2/product/category?access-token={token}&{name}&{expand}]

### List all Product Categories [GET]

+ Parameters

    + token
    + name (optional, string)
    + expand (optional, string) - products

+ Response 200 (application/json)

    + Attributes (array[ProductCategory])

### Create Product Category [POST]

+ Parameters

    + token

+ Request (application/json)

        {
            "name": "New Category",
            "parent_id": 2
        }

+ Response 200 (application/json)

    + Attributes (ProductCategory)

## Product Category [/v2/service/category/{id}?access-token={token}]

### Get Product Category [GET]

+ Parameters

    + id
    + token

+ Response 200 (application/json)

    + Attributes (ProductCategory)

### Update Product Category [PUT]

+ Parameters

    + id
    + token

+ Request (application/json)

        {
            "name": "New Category",
            "parent_id": 2
        }

+ Response 200 (application/json)

    + Attributes (ProductCategory)

## Delete Product Category [DELETE]

+ Response 204 (application/json)

+ Response 500 (application/json)

## Product Collection [/v2/product?access-token={token}&{expand}&{name}&{division_id}&{pagination}]

### List all Warehouse Products [GET]

+ Parameters

    + token (required, string) - Access token
    + name (optional, string) - Filter by name
    + division_id (optional, integer) - Filter by Division ID
    + pagination (optional, integer, `20`) - Page size, set 0 to disable pagination
    + expand (optional, string) - unit,category

+ Response 200 (application/json)

    + Attibutes (array[Product])

## Product Collection Grouped By Category [/v2/product/categories?access-token={token}]

### List all Products [GET]

+ Parameters

    + token (required, string) - Access token

+ Response 200 (application/json)

    + Attributes (array[ProductCategory])
    

## Medical Document Template Collection [/v2/document/template?access-token={token}&{expand}]

### Retrieve a Document Template Collection [GET]

+ Parameters

    + expand (optional, string) - documentForm

+ Response 200 (application/json)

    + Attributes (array[MedicalDocumentTemplate])

## Medical Document Template [/v2/document/template/{id}?access-token={token}&{expand}]

### Retrieve a Document Template [GET]

+ Parameters

    + id - Template ID
    + expand (optional, string) - documentForm

+ Response 200 (application/json)

    + Attributes (MedicalDocumentTemplate)
    
### Delete a Document Template [DELETE]

+ Parameters

    + id - Template ID

+ Response 204 (application/json)


### Create a Document Template [POST]

+ Parameters

    + id - Document Form ID
    + expand (optional, string) - documentForm

+ Request (application/json)

        {
            "name": "sdasdada",
            "dentalCard": [
                {
                    "number": 48,
                    "mobility": 10,
                    "diagnosis_id": 420
                }
            ],
            "some_value": 1,
            "treatment_plan": "sdasdasdas"
        }

+ Response 200 (application/json)

    + Attributes (MedicalDocumentTemplate)

### Update a Document Template [PUT]

+ Parameters

    + id - Template ID
    + expand (optional, string) - documentForm

+ Request (application/json)

        {
            "name": "sdasdada",
            "dentalCard": [
                {
                    "number": 48,
                    "mobility": 10,
                    "diagnosis_id": 420
                }
            ],
            "some_value": 1,
            "treatment_plan": "sdasdasdas"
        }

+ Response 200 (application/json)

    + Attributes (MedicalDocumentTemplate)


## Public Staff [/v2/public/staff?{division_id}&{expand}&{pagination}]

### Retrieve Employers [GET]

+ Parameters

    + division_id (optional, number)
    + expand (optional, string) - divisions
    + pagination (optional, integer)    

+ Response 200 (application/json)

    + Attributes(PublicStaff)


## Public Services [/v2/public/services?{staff_id}&{pagination}]

### Retrieve Services [GET]

+ Parameters

    + staff_id (required, number)
    + pagination (optional, integer)    

+ Response 200 (application/json)

    + Attributes(PublicService)


## Public Schedule [/v2/public/schedule?{division_id}&{staff_id}&{start_at}&{pagination}]

### Retrieve Schedule [GET]

+ Parameters

    + staff_id (required, number)
    + division_id (required, number)
    + start_at (required, string) - `yyyy-mm-dd`
    + pagination (optional, integer)    

+ Response 200 (application/json)

    + Attributes(StaffSchedule)
    

## Public Order [/v2/public/order]

### Create Order [POST]    

+ Request (application/json)

        {
            "datetime": "2018-06-05 13:00",
            "staff_id": 1,
            "division_id": 1,
            "customer_name": "John",
            "customer_phone": "+77013115115",
            "service_id": 1
        }

+ Response 201 (application/json)
+ Response 422 (application/json)
        
## Timetable Events [/v2/order/events?access-token={token,expand,start,end,staffs,position_id}]

### Retrieve an Order Collection as Events [GET]

+ Parameters

    + expand (optional, string) - contactCustomers,customer,files,documents,history,medCard,payments,products,services,staff,referrer,insuranceCompany
    + start (optional, string) - "2017-06-02",
    + end (optional, string) - "2017-06-03",
    + staffs (optional, array) - [{staff_id}],
    + position_id (optional, number) - 1

+ Response 200 (application/json)

    + Attributes (array[Order])


## Timetable Resources [/v2/staff/resources?access-token={token,expand,start,end,staffs,position_id}]

### Retrieve a Staff Collection as Resources [GET]

+ Parameters

    + division_id (required, integer)
    + date (required, integer)
    + staffs (optional, array) - [{staff_id}]
    + position_id (optional, number) - 1

+ Response 200 (application/json)

    + Attributes

        + id
        + eventClassName
        + name
        + position
        + schedule


## Data Structures

### Company

+ id: 30 (number)
+ name: `Клиника "MyCrm"` (string)
+ head_name: `Имя` (string)
+ head_surname: `Фамилия` (string)
+ head_patronymic: `Отчество` (string)
+ status: 1 (number)
+ status_label: `активный` (string)
+ logo_id: 1 (number)
+ logo_path: http://api.mycrm.loc/image/tem/por/ary/temporary_image.jpg (string)
+ category_id: 124 (number)
+ category_label: `Стоматология` (string)
+ publish: 1 (number)
+ balance: 153 (number)
+ last_payment: 2017-11-21 (string)
+ tariff: 0 (number)
+ calculation_method: 1 (number)
+ address: `Some address` (string)
+ iik: `company iik` (string)
+ bank: `bank name` (string)
+ bin: `bin` (string)
+ bik: `bik` (string)
+ phone: `phone number` (string)
+ license_issued: `` (string)
+ license_number: `` (string)
+ widget_prefix: `30` (string)
+ widget_url: `http://30.online.mycrm.kz` (string)
+ file_manager_enabled: true (boolean)
+ show_referrer: true (boolean)
+ interval: 5 (number)
+ online_start: '08:00' (string)
+ online_finish: '20:00' (string)
+ cashback_percent: 10 (number)
+ category (ServiceCategory)
+ cashes (array[Cash])
+ positions (array[Position])
+ divisions (array[Division])

### ServiceCategory

+ id: 124 (number),
+ name: `Стоматология` (string)
+ division_count: 54 (number)
+ parent_category_id: 1 (number)
+ services (array[Service])
+ subcategories (array[SubServiceCategory])

### SubServiceCategory

+ id: 1 (number)
+ name: `Волосы` (string)
+ image: https://crm.mycrm.kz/image/cat/ego/ry_/category_volosy.jpg (string)
+ services (array[SubServiceCategoryService])

### SubServiceCategoryService

+ id: 2 (number)
+ name: `Укладка волос` (string)
+ division_count: 66 (number)

### Cash

+ id: 3 (number)
+ name: `Касса` (string)
+ type: 0 (number)
+ init_money: 0 (number)
+ comments: `` (string)
+ is_deletable: true (boolean)
+ division_id: 49 (number)
+ status: 1 (number)

### Position

+ id: 49 (number)
+ name: `Мужской мастер` (string)
+ company_id: 30 (number)
+ description: `` (string)
+ commentCategories (array[CommentCategory])
+ documentForms (array[DocumentForm])

### CommentCategory

+ id: 1 (number)
+ name: `Жалобы` (string)
+ parent_id: 1 (number)
+ service_category_id: 124 (number)

### DocumentForm

+ id: 8 (number)
+ name: `Альвеолит (приложение к медицинской карте стоматологического больного)` (string)
+ has_dental_card: true (boolean)

### Phone

+ value: `+7 777 202 22 27` (string)

### Staff

+ id: 748 (number)
+ name: `Абылай` (string)
+ rating: 3.0 (number)
+ surname: `Абилов` (string)
+ image: https://crm.mycrm.kz/image/image?id=392&size=200 (string)
+ description: `Staff description` (string)
+ position_id: 49 (string)

### Payment

+ id: 1 (number)
+ name: `Наличными` (string)
+ type: 3 (number)

### Division

+ id: 49 (number)
+ address: `бул. Бухар Жырау, 26/1, уг. ул. Шагабутдинова` (string)
+ category_id: 14 (number)
+ city_id: 1 (number)
+ city_name: `Алматы` (string)
+ country_id: 72 (number)
+ country_name: `Казахстан` (string)
+ company_id: 30 (number)
+ description: `Company Desciprtion` (string)
+ key: `ETEDqh6F9Vn2` (string)
+ latitude: 43.23 (number)
+ longitude: 76.92 (number)
+ name: `Стоматология "MyCrm"` (string)
+ phone: `+7 747 623 57 41` (string)
+ rating: 4.25 (number)
+ status: 1 (number)
+ status_name: `активный` (string)
+ status_list
+ url: `www.fortest.kz` (string)
+ working_finish: `22:00:00` (string)
+ working_start: `09:00:00` (string)
+ phones (array[Phone])
+ staffs (array[Staff])
+ payments (array[Payment])
+ company (Company)
+ settings (DivisionSettings)

### DivisionSettings

+ id: 1 (number)
+ division_id: 76 (number)
+ notification_time_before_lunch: `19:00:00` (string)
+ notification_time_after_lunch: `12:00:00` (string)

### Referrer

+ id: 273 (number)
+ name: `Абай Жазбулганов` (string)

### InsuranceCompany

+ id: 1 (number)
+ name: `АО «СК «Евразия»` (string)
+ is_enabled: true (boolean)

### Order

+ id: 117160 (number)
+ className: `canceled_event` (string)
+ color: `color5` (string)
+ company_customer_id: 184494 (number)
+ company_cash_id: 255 (number)
+ staff_fullname: `Ниязбекова Гульнар` (string)
+ staff_position: `Хирург` (string)
+ datetime: `2018-05-05 09:00:00` (string)
+ division_id: 219 (number)
+ editable: true (boolean)
+ end: `2018-05-05 09:15:00` (string)
+ hours_before: 0 (string)
+ insurance_company_id: 12 (number)
+ note: `напомнить пациентке о проф.осмотре (string) прошло пол года (string) позвонить и записать ее` (string)
+ products_discount: 0 (number)
+ referrer_id: 12 (number)
+ resourceId: 1092 (number)
+ staff_id: 1092 (number)
+ start: `2018-05-05 09:00:00` (string)
+ status: 0 (number)
+ title: `Ниязбекова Гульнар\n+7 701 222 44 30\nповторный прием\n'напомнить пациентке о проф.осмотре, прошло пол года, позвонить и записать ее'\n` (string)
+ files (array[File])
+ documents (array[OrderDocument])
+ customer (Customer)
+ cash (Cash)
+ history
+ medCard
+ payments
+ services

### File

+ id: 1 (number)
+ path: http://api.mycrm.loc/image/tem/por/ary/temporary_image.jpg (string)
+ name: `temporary_image` (string)
+ extension: `jpg` (string)
+ created_at: `2018-05-05 09:15:00` (string)

### OrderDocument

+ id: 1 (number)
+ date: `2017-12-12` (string)
+ link: http://crm.mycrm.kz/path_to_some_file (string)
+ templateName: `Договор на проведение операции по дентальной имплантации зубов` (string)
+ userName: `Anuar Tastembekov` (string)

### Customer

+ id: 673 (number)
+ name: `Бауржан` (string)
+ lastname: `` (string)
+ patronymic: `` (string)
+ phone: `+7 747 623 57 41` (string)
+ email: `tastembekov.anuar@mail.ru` (string)
+ gender: 1 (number)
+ gender_title: `Не указано` (string)
+ birth_date: `2019-01-01` (string)
+ iin: `900613350146` (string)
+ id_card_number: `900613350146` (string)
+ address: `Samen batyr 1A` (string)
+ balance: 3500 (number)
+ city: `Almaty` (string)
+ comments: `Some comment` (string)
+ discount: 10 (number)
+ employer: `Google inc.` (string)
+ job: `Lawyer` (string)
+ sms_birthday: true (boolean)
+ sms_birthday_title: `Да` (string)
+ sms_exclude: false (boolean)
+ sms_exclude_title: `Нет` (string)
+ source_id: 1 (number)
+ cashback_percent: 10 (number)
+ cashback_balance: 1000 (number)
+ insurance_company_id: {insurance_company_id} (number)
+ insurance_expire_date: `2019-01-01` (string)
+ insurance_policy_number: `1234567890` (string)
+ insurer: `ТОО Kcell`
+ debt: 3500 (number)
+ deposit: 0 (number)
+ revenue: 3500 (number)
+ source (CustomerSource)
+ canceledOrders: 1 (number)
+ finishedOrders: 1 (number)
+ lastOrder (Order)
+ categories (array[CustomerCategory])
+ files (array[File])
+ documents (array[OrderDocument])
+ orders (array[Order])

### UserSchedule

+ id: 748 (number)
+ start: `09:00` (string)
+ end: `22:00` (string)
+ break_start: `12:00` (string)
+ break_end: `13:00` (string)
+ orders (array[Order])
+ staff (Staff)

### User

+ id: 748 (number)
+ username: `+7 747 623 57 41` (string)
+ company_id: 18 (number)
+ google_refresh_token: `1234567890` (string)
+ status: 1 (number)

### SMSTemplate

+ id: 208 (number)
+ label: `Поздравление клиенту с Днём Рождения` (string)
+ key: `6` (string)
+ template: `Поздравляем Вас с Днём Рождения! Желаем всех благ и хорошего настроения! С Уважением, %COMPANY_NAME%` (string)
+ is_enabled: false (boolean)
+ is_delayed: false (boolean)
+ quantity: 5 (number)
+ quantity_type: 1 (number)

### PaymentAction

+ id: 32642 (number)
+ value: 100 (number)
+ currency: 398 (number)
+ code: `v4DFQQcck-Q7MofXhp4hwnoXkaKmEd76` (string)
+ created_time: `2017-11-25 14:06:49` (string)
+ confirmed_time: `2017-11-25 14:06:49` (string)
+ description: `Поплнение баланса 'Клиника "MyCrm"` (string)
+ message: `Some message` (string)

### CustomerSource

+ id: 47 (number)
+ name: `рекомендации` (string)
+ company_id: 176 (number)
+ count: 111 (number)

### CustomerCategory

+ id: 25 (number)
+ name: `Алматы` (string)
+ color: `#888888` (string)
+ company_id: 30 (number)
+ discount: 20 (number)

### Country

+ id: 2
+ name: `Абхазия`
+ active: false
+ cities (array[City])

### City

+ id: 1
+ name: `Алматы`
+ country_id: 72
+ country_name: `Казахстан`

### Service

+ id: 4873 (number)
+ publish: true (boolean)
+ division_id: 124 (number)
+ price: 30000 (number)
+ average_time: 70 (number)
+ service_name: `Макияж` (string)
+ description: `Some Description` (string)
+ price_max: 5000 (number)
+ status: 1 (number)
+ is_trial: false (boolean)
+ products (array[ServiceProduct])
+ insurance-companies (array[InsuranceCompanyPrice])

### ServiceProduct

+ id: 4873 (number)
+ division_service_id: 124 (number)
+ product_id: 30000 (number)
+ quantity: 70 (number)
+ product (array[Product])

### Product

+ id: 228 (number)
+ product_id: 228 (number)
+ barcode: `` (string)
+ description: `` (string)
+ min_quantity: 10 (number)
+ quantity: 17 (number)
+ name: `Cream` (string)
+ price: 100 (number)
+ sku: `` (string)
+ vat: 0 (number)
+ category_id: 13 (number)
+ unit_id: 4 (number)
+ manufacturer_id: 1 (number)
+ company_id: 30 (number)
+ package_size: 10 (number)
+ stock_unit_id: 1 (number)
+ purchase_price: 0 (number)
+ status: 1 (number)
+ division_id: 4 (number)
+ category (ProductCategory)

### ProductCategory

+ id: 214 (number)
+ company_id: 30 (number)
+ name: `gdfgd` (string)
+ parent_id: 13 (number)
+ products (array[Product])

### InsuranceCompanyPrice

+ id: 1 (number)
+ division_service_id: 16551 (number)
+ insurance_company_id: 1 (number)
+ price: 1000 (number)
+ price_max: 2000 (number)

### CustomerLoyalty

+ id: 42 (number)
+ event: 0 (number)
+ amount: 42000 (number)
+ discount: 42 (number)
+ rank: 1 (number)
+ category_id: 2 (number)
+ mode: 0 (number)
+ company_id: 42 (number)
+ trigger_title: `после выручки 42 000 тенге` (string)
+ event_title: `скидка 42%` (string)

### OrderHistory

+ created_at: `2018-05-05 06:00:00` (string)
+ action: `Создан` (string)
+ datetime: `2018-05-05 09:00:00` (string)
+ status: `ожидание клиента` (string)
+ user: `Azamat` (string)
+ staff_name: `Миржан Арыстанбеков` (string)
+ status: 1 (number)

### OrderDocumentTemplate

+ id: 1 (number)
+ name: `Согласие на отбеливание` (string)
+ filename: `Согласие_на_отбеливание.docx` (string)
+ category_id: 124 (number)
+ company_id: 181 (number)
+ path: /uploads/0gE/LvR/YOf/0gELvRYOf4V8t0OQpon_hU9kI9FmgJHl.xls (string)

### StaffSchedule

+ id: 14 (number)
+ staff_id: 748 (number)
+ start_at: `2017-12-08 09:00:00` (string)
+ end_at: `2017-12-08 22:00:00` (string)
+ division_id: 49 (number)

### MedicalDocumentForm

+ id: 1 (number)
+ name: `Name` (string)
+ has_dental_card: true (boolean)
+ has_services: true (boolean)
+ elements (array[MedicalDocumentElement])

### MedicalDocumentElement

+ id: 1 (number)
+ group_id: 3 (number)
+ label: `План лечения` (string)
+ key: `treatment_plan` (string)
+ raw_id: 3 (number)
+ order: 1 (number)
+ options: `[]` (string)
+ type: `text` (string)
+ search_url: `diagnosis?service_category_id=124` (string)
+ depends_on: `diagnosis_id` (string)

### MedicalDocument

+ id: 13 (number)
+ customer_id: 190 (number)
+ document_form_id: 1 (number)
+ manager_id: 15 (number)
+ staff_id: 115 (number)
+ created_at: `2017-12-08 09:00:00` (string)
+ form (MedicalDocumentForm)
+ customer (Customer)
+ dentalCard (array[DentalCardElement])
+ manager (Staff)
+ services (array[DocumentService])
+ staff (Staff)
+ values (array[DocumentValue])

### DentalCardElement

+ diagnosis_id: 11 (number)
+ number: 47 (number)
+ mobility: 2 (number)
+ diagnosis (ToothDiagnosis)
+ document (MedicalDocument)

### DocumentValue

+ document_id: 1 (number)
+ document_form_element: 1 (number)
+ key: `treatment_plan` (string)
+ value: `Edited text` (string)

### DocumentService

+ document_id: 1 (number)
+ service_id: 1 (number)
+ price: 1 (number)
+ quantity: 1 (number)
+ discount: 0 (number)
+ service (Service)

### MedicalDocumentTemplate

+ id: 1 (number)
+ created_at: `2018-03-12 09:34:57` (string)
+ created_by: 8 (number)
+ document_form_id: 10 (number)
+ name: `sdasdada` (string)
+ dentalCard (array[MedicalDocumentTemplateDentalCard])
+ values (array[MedicalDocumentTemplateValue])

### MedicalDocumentTemplateDentalCard

+ number: 48 (number)
+ mobility: 10 (number)
+ diagnosis_id: 420 (number)

### MedicalDocumentTemplateValue

+ key: `toilet_value` (string)
+ value: 1 (number)

### ToothDiagnosis

+ id: 520 (number)
+ company_id: 176 (number)
+ name: `корень` (string)
+ abbreviation: `R` (string)
+ color: `#F00` (string)

### News

+ id: 2 (number)
+ text: `Lorem ipsum dolor 2` (string)
+ link: https://link-to-changelog-2.com (string)

### CreateOrderForm

+ company_cash_id: 255 (number, required)
+ customer_name: `Гульнар` (string, required)
+ datetime: `2018-05-05 09:00` (string, required)
+ staff_id: 1092 (number, required)
+ hours_before: 0 (number, required)
+ division_id: 219 (number, required)
+ services: (array[OrderFormService], required)
+ company_customer_id: 150515 (number)
+ customer_surname: `Ниязбекова` (string)
+ customer_patronymic: `Ниязбековна` (string)
+ customer_phone: `+7 701 222 44 30` (string)
+ customer_birth_date: `2000-04-20` (string)
+ customer_medical_record_id_: `200420` (string)
+ customer_gender: 2 (number)
+ customer_source_name: `Some source name to create` (string)
+ customer_source_id: 1 (number)
+ referrer_id: 23 (number)
+ referrer_name: `new referrer name` (string)
+ insurance_company_id: 1 (number)
+ note: `напомнить пациентке о проф.осмотре, прошло пол года, позвонить и записать ее` (string)
+ contacts: (array[OrderFormContact])
+ payments: (array[OrderFormPayment])
+ products: (array[OrderFormProduct])
+ categories: (array)

### OrderFormContact

+ id: 150515 (number)
+ name: `Mr. Burbery` (string)
+ phone: `+7 701 381 71 15` (string)

### OrderFormPayment

+ amount: 8000 (number, required)
+ payment_id: 1 (number, required)

### OrderFormService

+ division_service_id: 1550 (number, required)
+ quantity: 1 (number, required)
+ discount: 10 (number)
+ price: 5000 (number)
+ duration: 30 (number)
+ order_service_id: 356 (number)

### OrderFormProduct

+ price: 200 (number, required)
+ quantity: 3 (number, required)
+ product_id: 1550 (number, required)

### Cash
+ id: 198 (number)
+ comments: null (string)
+ division_id: 205 (number, required)
+ init_money: 0 (number)
+ name: `Касса` (string, optional)

### PublicStaff

+ id: 748 (number)
+ name: `Абылай` (string)
+ surname: `Абилов` (string)
+ birth_date: `1985-06-06` (string)
+ divisions: (array[PublicDivision])

### PublicDivision

+ id: 49 (number)
+ address: `бул. Бухар Жырау, 26/1, уг. ул. Шагабутдинова` (string)
+ city_name: `Алматы` (string)
+ country_name: `Казахстан` (string)
+ name: `Стоматология "MyCrm"` (string)
+ working_finish: `22:00:00` (string)
+ working_start: `09:00:00` (string)

### PublicService

+ id: 49 (number)
+ name: `Консультация` (string)
+ price: 2000 (number)
+ duration: 30 (number)

### WebCall

+ from_date: `2016-06-06 12:00:00` (string)
+ to_date: `2016-06-07 12:00:00` (string)
+ incoming: 10 (number)
+ outgoing: 5 (number)
+ missed: 2 (number)
+ incoming_duration: 123 (number)
+ outgoing_duration: 23 (number)
+ first_calls: 3 (number)
+ missed_first_calls: 1 (number)
