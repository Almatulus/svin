<?php

namespace core\models\warehouse;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * ProductSearch represents the model behind the search form about `core\models\warehouse\Product`.
 */
class ProductSearch extends Product
{
    public $categoryName;
    public $status = Product::STATUS_ENABLED; // Instead of active

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'category_id', 'unit_id', 'manufacturer_id'], 'integer'],
            [['barcode', 'categoryName', 'description', 'name', 'sku', ], 'safe'],
            [['quantity', 'price', 'vat'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Product::find()
            ->company()
            ->permitted()
            ->joinWith(['category cat']);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'name',
                    'categoryName' => [
                        'asc' => ['cat.name' => SORT_ASC],
                        'desc' => ['cat.name' => SORT_DESC],
                        'default' => SORT_ASC
                    ]
                ],
                'defaultOrder' => ['name' => SORT_ASC]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['{{%warehouse_product}}.category_id' => $this->category_id]);
        $query->andFilterWhere(['{{%warehouse_product}}.status' => $this->status]);
        $query->andFilterWhere([
            'OR',
            ['~*', '{{%warehouse_product}}.name', $this->name],
            ['like', '{{%warehouse_product}}.barcode', $this->name],
            ['like', '{{%warehouse_product}}.sku', $this->name]
        ]);

        return $dataProvider;
    }

    public function export($products) {

        ob_start();

        $ea = new \PHPExcel(); // ea is short for Excel Application
        $ea->getProperties()
           ->setCreator('MyCRM')
           ->setTitle('PHPExcel')
           ->setLastModifiedBy('MyCRM')
           ->setDescription('')
           ->setSubject('')
           ->setKeywords('excel php')
           ->setCategory('');
        $ews = $ea->getSheet(0);
        $ews->setTitle('Товары');

        $data  = [
            [
                'Перечень товаров',
                '',
                'создано:',
                date("Y-m-d H:i")
            ]
        ];

        $oldCategory = null;
        $product = new Product();
        $titleRows = [];

        foreach ($products as $iter) {
            if ($iter->category_id != $oldCategory) {
                $categoryTitle = isset($iter->category->name) ? $iter->category->name : 'Не указано';
                $data[] = [];
                $data[] = [$categoryTitle];
                $data[] = [];

                $data[] = [
                    $product->getAttributeLabel('name'),
                    $product->getAttributeLabel('manufacturer_id'),
                    $product->getAttributeLabel('price'),
                    $product->getAttributeLabel('vat'),
                    $product->getAttributeLabel('quantity'),
                    $product->getAttributeLabel('min_quantity'),
                    $product->getAttributeLabel('types'),
                    $product->getAttributeLabel('description'),
                    $product->getAttributeLabel('sku'),
                    $product->getAttributeLabel('barcode'),
                ];

                $titleRows[] = sizeof($data);
                $oldCategory = $iter->category_id;
            }

            $data[] = [
                $iter->name,
                $iter->manufacturer->name ?? null,
                Yii::$app->formatter->asDecimal($iter->price),
                $iter->vat,
                Yii::$app->formatter->asDecimal($iter->quantity),
                $iter->min_quantity,
                $iter->getTypesTitle(";"),
                $iter->description,
                $iter->sku,
                $iter->barcode
            ];
        }

        $ews->fromArray($data, ' ', 'A1');
        for ($ch = 'A'; $ch <= 'J'; $ch++) {
            $ews->getColumnDimension($ch)->setAutoSize(true);
        }

        $ews->getStyle('A1')->getFont()->setBold(true);
        foreach ($titleRows as $key => $titleRow) {
            $ews->getStyle("A{$titleRow}:J{$titleRow}")->getFont()->setBold(true);
        }

        header('Content-Type: application/vnd.ms-excel');
        $filename = "Товары_" . date("d-m-Y-His") . ".xls";
        header('Content-Disposition: attachment;filename=' . $filename . ' ');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($ea, 'Excel5');
        $objWriter->save('php://output');

        ob_end_flush();
    }
}
