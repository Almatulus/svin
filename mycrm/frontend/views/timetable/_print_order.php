<div id="order-print" hidden>
    <style>
        @page {
            size: auto;
            margin: 0mm;
        }

        @media (max-width: 305px) {
            .order-print-content p,
            .order-print-content table th,
            .order-print-content table td {
                font-size: 10px;
            }

            .order-print-content .date {
                font-size: 9px;
            }

            .order-print-content h4 {
                font-size: 11px;
            }

            .order-print-content table td {
                vertical-align: bottom;
            }

            .order-print-content table.total th,
            .order-print-content table.total td {
                font-size: 9px;
            }

            .order-print-content table.services th,
            .order-print-content table.services td {
                display: none;
                padding-left: 0;
            }

            .order-print-content table.services td:first-child,
            .order-print-content table.services td:last-child,
            .order-print-content table.services th:first-child,
            .order-print-content table.services th:last-child {
                display: table-cell;
            }

            .order-print-content .division-info .logo {
                width: 30%;
            }
        }

        @media (min-width: 306px) {
            .order-print-content p,
            .order-print-content table th,
            .order-print-content table td {
                font-size: 11px;
            }

            .order-print-content h4 {
                font-size: 12px;
            }

            .order-print-content table td,
            .order-print-content table th {
                padding: 4px;
            }

            .order-print-content .services {
                border-collapse: collapse;
            }

            .order-print-content .services td, .order-print-content .services th {
                border: 1px solid #000;
            }

            .order-print-content .division-info .logo {
                width: 20%;
            }
        }

        @media print {
            .order-print-content {
                margin: 0 auto;
                max-width: 600px;
            }

            .order-print-content .division-info {
                display: flex;
                flex-direction: row;
                margin-bottom: 20px;
                align-items: center;
            }

            .order-print-content .division-info .logo img {
                width: 100%;
            }

            .order-print-content .division-info .details {
                padding-left: 4px;
            }

            .order-print-content .division-info p {
                margin: 0;
            }

            .order-print-content table {
                text-align: left;
            }

            .order-print-content .signs {
                margin-bottom: 10px;
            }

            .order-print-content .total {
                margin-bottom: 20px;
            }

            .order-print-content .order-info {
                text-align: center;
                margin-bottom: 10px;
            }

            .order-print-content h4 {
                margin-bottom: 4px;
            }

            .order-print-content .date {
                font-size: 10px;
            }

            .bold {
                font-weight: 600;
            }
        }
    </style>
    <div class="order-print-content">
        <div class="division-info">
            <div class="logo"><img src=""/></div>
            <div class="details">
                <p class="division-name"></p>
                <p class="division-address"></p>
                <p class="staff-name"></p>
            </div>
        </div>

        <h4 class="order-info">
            Заказ № <span class="order-key"></span> от <span class="order-datetime"></span>
        </h4>

        <table class="services" width="100%">
            <thead>
                <tr>
                    <th>Наименование услуги</th>
                    <th>Цена</th>
                    <th>Кол-во</th>
                    <th>Скидка</th>
                    <th>Сумма</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>

        <div class="teeth" hidden>
            Описание: <span class="order-teeth"></span>
        </div>

        <div class="total">
            <h4>Итого:</h4>
            <table>
                <tbody>
                    <tr>
                        <td>Сумма счета:</td>
                        <td class="bold order-price"></td>
                    </tr>
                    <tr>
                        <td>Оплачено:</td>
                        <td class="bold order-paid"></td>
                    </tr>
                    <tr>
                        <td>Долг:</td>
                        <td class="bold order-debt"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="signs">
            <table width="100%">
            <tbody>
                <tr>
                    <td width="50%"></td>
                    <td width="50%" align="RIGHT">Клиент:_____________________</td>
                </tr>
                <tr>
                    <td width="50%"></td>
                    <td width="50%" align="RIGHT" class="bold customer-fullname"></td>
                </tr>
            </tbody>
            </table>
        </div>

        <div class="date">
            Сформировано <span class="date-created"></span>
        </div>
    </div>
</div>
