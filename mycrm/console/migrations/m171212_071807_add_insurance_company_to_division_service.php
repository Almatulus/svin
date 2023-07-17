<?php

use yii\db\Migration;

/**
 * Class m171212_071807_add_insurance_company_to_division_service
 */
class m171212_071807_add_insurance_company_to_division_service extends Migration
{
    private $data = [
        'АО "Чартис Казахстан cтраховая компания"',
        'АО "СК "Казкоммерц-Полис" (ДО АО "Казкоммерцбанк")',
        'АО "Страховая компания "Лондон-Алматы"',
        'АО "Kaspi Страхование" (бывшее наименование - Дочерняя компания АО "Kaspi Bank" "СК "Алматинская Международная Страховая Группа")',
        'АО "ДО АО "Цеснабанк" Страховая Компания "Цесна Гарант" (прежнее наименование АО "СК "Сак Иншуранс")',
        'АО "Дочерняя компания Народного Банка Казахстана по страхованию жизни "Халык-Life"',
        'АО "Дочерняя Страховая компания Народного банка Казахстана "Халык-Казахинстрах"',
        'АО "Зерновая страховая компания"',
        'АО "КК ЗиМС "ИНТЕРТИЧ"',
        'АО "Компания по страхованию жизни  "НОМАД LIFE" (прежнее наименование АО "КСЖ "Астана-Финанс")',
        'АО "Компания по страхованию жизни "Standard Life"  (прежнее наименование АО "КСЖ "Grandes")',
        'АО "Компания по страхованию жизни "Азия Life" ',
        'АО "Компания по страхованию жизни "Государственная аннуитетная компания"',
        'АО "Компания по страхованию жизни "Казкоммерц-Life" (ДО АО "Казкоммерцбанк")',
        'АО "Нефтяная страховая компания"',
        'АО "СК "Kompetenz" (прежнее наименование - ДО  Европейского акционерного общества "Allianz S.E." АО СК "Allianz Kazakhstan")',
        'АО "СК "Альянс Полис"',
        'АО "СК "АСКО"',
        'АО "СК "Виктория"',
        'АО "СК "Коммеск-Өмір"',
        'АО "СК "НОМАД Иншуранс"',
        'АО "СК "Салем" (прежнее наименование АО "СК "Алатау")',
        'АО "СК "ТрансОйл"',
        'АО "Страховая  компания "Казахмыс"',
        'АО "Страховая компания "Amanat" (прежнее наименование АО "Страховая компания "Amanat Insurance")',
        'АО "Страховая Компания "Сентрас Иншуранс"',
        'АО "Экспортная страховая компания "KazakhExport" (прежнее наименование – Экспортно- кредитная страховая корпорация "КазЭкспортГарант")',
        'АО "ДО АО "Нурбанк" СК "Нурполис"',
        'АО "Страховая компания "Sinoasia B&R (Синоазия БиЭндАр)" (прежнее наименование - АО "МСК "Архимедес Казахстан")',
        'АО "Страховая компания "STANDARD" (прежнее наименование - АО СК "Астана- Финанс")',
        'АО "Страховая компания "Trust Insurance"',
        'АО "Страховая компания "Евразия"',
        'АО Компания по Страхованию Жизни "Европейская Страховая Компания" (бывшее наименование -  АО "Компания по Страхованию Жизни "РРF Insurance")',
        'Samal Medical Assistance',
        'SOS Medical Assistance',
        'Медикер Ассистанс',
        'Центр Неврологии и Эпилептологии',
    ];

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%insurance_companies}}', [
            'id'   => $this->primaryKey(),
            'name' => $this->string()
        ]);

        foreach ($this->data as $insuranceName) {
            $this->insert("{{%insurance_companies}}", ["name" => $insuranceName]);
        }

        $this->execute("ALTER TABLE {{%company_insurances}} ALTER COLUMN name DROP NOT NULL");

        $this->alterCompanyInsuranceTable();

        $this->addColumn('{{%division_services}}', 'insurance_company_id', $this->integer()->unsigned());
        $this->addForeignKey('fk-insurance-company', '{{%division_services}}', 'insurance_company_id',
            '{{%insurance_companies}}', 'id');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropColumn('{{%company_insurances}}', 'insurance_company_id');
        $this->dropColumn('{{%division_services}}', 'insurance_company_id');
        $this->dropTable('{{%insurance_companies}}');
    }

    public function getMapper()
    {
        return [
            "Interteach"                       => 'АО "КК ЗиМС "ИНТЕРТИЧ"',
            "Казком Полис"                     => 'АО "СК "Казкоммерц-Полис" (ДО АО "Казкоммерцбанк")',
            "Медикер"                          => 'Медикер Ассистанс',
            "Архимедос"                        => 'АО "Страховая компания "Sinoasia B&R (Синоазия БиЭндАр)" (прежнее наименование - АО "МСК "Архимедес Казахстан")',
            "Архимедес"                        => 'АО "Страховая компания "Sinoasia B&R (Синоазия БиЭндАр)" (прежнее наименование - АО "МСК "Архимедес Казахстан")',
            "ЕврАзия"                          => 'АО "Страховая компания "Евразия"',
            "НСА"                              => 'АО "Нефтяная страховая компания"',
            "НСК"                              => 'АО "Нефтяная страховая компания"',
            "Центр Неврологии и Эпилептологии" => 'Центр Неврологии и Эпилептологии',
            "Самалмедикалассистанс"            => 'Samal Medical Assistance',
            "SOS медикалассистанс"             => 'SOS Medical Assistance',
            "ТОО \"Медикер\""                  => 'Медикер Ассистанс',
            "ТОО \"Самал Медикал Ассистанс\""  => 'Samal Medical Assistance',
            "ТОО \"Архимедес Казахстан\""      => 'АО "Страховая компания "Sinoasia B&R (Синоазия БиЭндАр)" (прежнее наименование - АО "МСК "Архимедес Казахстан")',
            "Sos medical assistanse"           => 'SOS Medical Assistance',
            "Sinoasia"                         => 'АО "Страховая компания "Sinoasia B&R (Синоазия БиЭндАр)" (прежнее наименование - АО "МСК "Архимедес Казахстан")',
            "ТОО \"Medical Assistance Group\" (Евразия)" => 'АО "Страховая компания "Евразия"',
        ];
    }

    private function alterCompanyInsuranceTable()
    {
        $this->addColumn("{{%company_insurances}}", "insurance_company_id", $this->integer()->unsigned());
        $this->addForeignKey('fk-insurance-company', '{{%company_insurances}}', 'insurance_company_id',
            '{{%insurance_companies}}', 'id');

        foreach ($this->getMapper() as $key => $map) {

            $insuranceCompanyID = \core\models\InsuranceCompany::find()->select('id')->where(['name' => $map])->scalar();

            if ($insuranceCompanyID) {
                $this->update("{{%company_insurances}}", [
                    'insurance_company_id' => $insuranceCompanyID
                ], ['name' => $key]);
            }
        }
    }
}
