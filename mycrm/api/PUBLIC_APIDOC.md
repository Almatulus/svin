FORMAT: 1A
HOST: https://api.mycrm.kz/

# API Mycrm

## Collection of Employers [/v2/public/staff?division_id={division_id}&expand={expand}&pagination={pagination}]

### Retrieve Employers [GET]

+ Parameters

    + division_id (optional, number)
    + expand (optional, string) - divisions
    + pagination (optional, integer)

+ Response 200 (application/json)

    + Attributes(PublicStaff)


## Collection of Services [/v2/public/staff/{staff_id}/service?pagination={pagination}]

### Retrieve Services [GET]

+ Parameters

    + staff_id (required, number)
    + pagination (optional, integer)    

+ Response 200 (application/json)

    + Attributes(PublicService)


## Public Schedule [/v2/public/schedule?division_id={division_id}&staff_id={staff_id}&start_at={start_at}&end_at={end_at}&pagination={pagination}]

### Retrieve Schedule [GET]

+ Parameters

    + staff_id (required, number)
    + division_id (required, number)
    + start_at (required, string) - `yyyy-mm-dd`
    + end_at (required, string) - `yyyy-mm-dd`
    + pagination (optional, integer)    

+ Response 200 (application/json)

    + Attributes(array[StaffSchedule])


## Public Order [/v2/public/order]

### Create Order [POST]    

+ Request (application/json)

    + Attributes (OrderCreateForm)

+ Response 201 (application/json)
+ Response 422 (application/json)

## Data Structures

### OrderCreateForm

+ datetime: `2018-06-05 13:00` (required, string)
+ staff_id: 1 (required, number)
+ division_id: 1 (required, number)
+ customer_name: `John` (required, string)
+ customer_phone: `+77013115115` (required, string)
+ service_id: 1 (required, number)

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

### StaffSchedule

+ id: 14 (number)
+ division_id: 49 (number)
+ staff_id: 748 (number)
+ start_at: `2017-12-08 09:00:00` (string)
+ end_at: `2017-12-08 22:00:00` (string)
