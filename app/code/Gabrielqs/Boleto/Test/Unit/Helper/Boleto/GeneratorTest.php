<?php

namespace Gabrielqs\Boleto\Test\Unit\Helper\Boleto;

use Magento\Framework\Exception\LocalizedException;
use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Address;
use \OpenBoleto\Agente as BoletoAgent;
use \OpenBoleto\AgenteFactory as BoletoAgentFactory;
use \Gabrielqs\Boleto\Helper\Boleto\Generator as Subject;
use \Gabrielqs\Boleto\Helper\Boleto\Data as BoletoHelper;

/**
 * Unit Testcase
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Boleto Agent Factory
     * @var BoletoAgentFactory
     */
    protected $_boletoAgentFactory = null;

    /**
     * @var BoletoHelper
     */
    protected $_boletoHelper = null;

    /**
     * @var String
     */
    protected $className = null;

    /**
     * @var \ReflectionMethod
     */
    protected $createBoletoAgentMethod = null;

    /**
     * @var \ReflectionMethod
     */
    protected $createBoletoObjectMethod = null;

    /**
     * @var ObjectManager
     */
    protected $objectManager = null;

    /**
     * @var Subject
     */
    protected $originalSubject = null;

    /**
     * @var Subject
     */
    protected $subject = null;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->className = Subject::class;
        $arguments = $this->getConstructorArguments();

        $this->subject = $this
            ->getMockBuilder($this->className)
            ->setConstructorArgs($arguments)
            ->setMethods(['_getBoletoExpiryDate', '_prepareOrderId'])
            ->getMock();

        $this->originalSubject = $this
            ->objectManager
            ->getObject($this->className, $arguments);

        $reflection = new \ReflectionClass($this->objectManager->getObject($this->className));
        $this->createBoletoAgentMethod = $reflection->getMethod('_createBoletoAgent');
        $this->createBoletoAgentMethod->setAccessible(true);
        $this->createBoletoObjectMethod = $reflection->getMethod('_createBoletoObject');
        $this->createBoletoObjectMethod->setAccessible(true);
    }

    protected function getConstructorArguments()
    {
        $arguments = $this->objectManager->getConstructArguments($this->className);

        $this->_boletoHelper = $this->getMockBuilder(BoletoHelper::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getBeneficiaryOfficialName',
                'getBeneficiaryTaxVat',
                'getBeneficiaryAddress',
                'getBeneficiaryPostcode',
                'getBeneficiaryCity',
                'getBeneficiaryRegion',
                'getDaysToExpire',
                'getAgencyCode',
                'getContractCode',
                'getAccountCode',
                'getDescription',
                'getInstructions',
                'getBankCode'
            ])
            ->getMock();
        $arguments['boletoHelper'] = $this->_boletoHelper;

        $this->_boletoAgentFactory = $this->getMockBuilder(BoletoAgentFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $arguments['boletoAgentFactory'] = $this->_boletoAgentFactory;

        return $arguments;
    }

    public function dataProviderTestGetBoletoHtmlHasExpectedOutput()
    {
        return [
            ['itau', 175, $this->_getBoletoItau()],
            ['bradesco', 9, $this->_getBoletoBradesco()]
        ];
    }


    /**
     * @param $bankCode
     * @param $contractCode
     * @param $expectedOutput
     * @dataProvider dataProviderTestGetBoletoHtmlHasExpectedOutput
     */
    public function testGetBoletoHtmlHasExpectedOutput($bankCode, $contractCode, $expectedOutput)
    {
        $this
            ->_boletoAgentFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->willReturnOnConsecutiveCalls(
                new BoletoAgent(
                    'Gabrielqs Tecnologia Design e Comunicação LTDA',
                    '21.039.394/0001-21',
                    'CLN 213 Bloco C Sala 204, Asa Norte',
                    '70846-030',
                    'Brasília',
                    'DF'
                ),
                new BoletoAgent(
                    'Roberto Marinho',
                    '922.392.231-98',
                    'Rua Comendador Gomes, nº 172, Próximo ao banco, Centro',
                    '01372-010',
                    'São Paulo',
                    'SP'
                )
            );
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryOfficialName')
            ->will($this->returnValue('Gabrielqs Tecnologia Design e Comunicação LTDA'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryTaxVat')
            ->will($this->returnValue('21.039.394/0001-21'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryAddress')
            ->will($this->returnValue('CLN 213 Bloco C Sala 204, Asa Norte'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryPostcode')
            ->will($this->returnValue('70846-030'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryCity')
            ->will($this->returnValue('Brasília'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getBeneficiaryRegion')
            ->will($this->returnValue('DF'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getAgencyCode')
            ->will($this->returnValue('0392'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getContractCode')
            ->will($this->returnValue($contractCode));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getAccountCode')
            ->will($this->returnValue('3938'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getBankCode')
            ->will($this->returnValue($bankCode));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getDescription')
            ->will($this->returnValue('Pedido efetuado na loja virtual Loja Gabrielqs V2'));
        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getInstructions')
            ->will($this->returnValue('Sr. Caixa, não receber após o vencimento.'));


        $billingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getVatId',
                'getStreet',
                'getPostcode',
                'getCity',
                'getRegionCode'
            ])
            ->getMock();
        $billingAddress
            ->expects($this->once())
            ->method('getVatId')
            ->will($this->returnValue('922.392.231-98'));
        $billingAddress
            ->expects($this->once())
            ->method('getStreet')
            ->will($this->returnValue(['Rua Comendador Gomes', 'nº 172', 'Próximo ao banco', 'Centro']));
        $billingAddress
            ->expects($this->once())
            ->method('getPostcode')
            ->will($this->returnValue('01372-010'));
        $billingAddress
            ->expects($this->once())
            ->method('getCity')
            ->will($this->returnValue('São Paulo'));
        $billingAddress
            ->expects($this->once())
            ->method('getRegionCode')
            ->will($this->returnValue('SP'));

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getBillingAddress',
                'getCustomerName',
                'getGrandTotal',
                'getIncrementId'
            ])
            ->getMock();
        $order
            ->expects($this->once())
            ->method('getBillingAddress')
            ->will($this->returnValue($billingAddress));
        $order
            ->expects($this->once())
            ->method('getCustomerName')
            ->will($this->returnValue('Roberto Marinho'));
        $order
            ->expects($this->once())
            ->method('getGrandTotal')
            ->will($this->returnValue(185.90));

        $this
            ->subject
            ->expects($this->once())
            ->method('_getBoletoExpiryDate')
            ->will($this->returnValue(new \DateTime('2016-09-10')));

        $this
            ->subject
            ->expects($this->once())
            ->method('_prepareOrderId')
            ->with($order)
            ->will($this->returnValue('00000125'));


        $return = $this->subject->getBoletoHtml($order);

        $this->assertEquals(
            preg_replace('/[^\da-z]/i', '', $return),
            preg_replace('/[^\da-z]/i', '', $expectedOutput)
        );
    }

    public function dataProviderTestCreateBoletoAgentThrowsExceptionOnInvalidData()
    {
        return [
            ['', '', '', '', '', ''],
            ['a', '', '', '', '', ''],
            ['a', 'a', '', '', '', ''],
            ['a', 'a', 'a', '', '', ''],
            ['a', 'a', 'a', 'a', '', ''],
            ['a', 'a', 'a', 'a', 'a', ''],
            ['', 'a', 'a', 'a', 'a', 'a'],
            ['', '', 'a', 'a', 'a', 'a'],
            ['', '', '', 'a', 'a', 'a'],
            ['', '', '', '', 'a', 'a'],
            ['', '', '', '', '', 'a'],
        ];
    }

    /**
     * @param $name
     * @param $taxvat
     * @param $address
     * @param $postcode
     * @param $city
     * @param $region
     * @dataProvider dataProviderTestCreateBoletoAgentThrowsExceptionOnInvalidData
     */
    public function testCreateBoletoAgentThrowsExceptionOnInvalidData(
        $name, $taxvat, $address, $postcode, $city, $region
    ) {
        $agentInfo = new \stdClass();
        $agentInfo->name = $name;
        $agentInfo->taxVat = $taxvat;
        $agentInfo->address = $address;
        $agentInfo->postcode = $postcode;
        $agentInfo->city = $city;
        $agentInfo->region = $region;

        $this->setExpectedException(LocalizedException::class);

        $this->createBoletoAgentMethod->invoke($this->subject, $agentInfo);
    }

    public function dataProviderTestCreateBoletoObjectReturnsExceptionOnInvalidBankCode()
    {
        return [
            ['laga'],
            [''],
            ['bancodobrasil'],
            ['cef'],
            [null]
        ];
    }

    /**
     * @param $bankCode
     * @dataProvider dataProviderTestCreateBoletoObjectReturnsExceptionOnInvalidBankCode
     */
    public function testCreateBoletoObjectReturnsExceptionOnInvalidBankCode($bankCode)
    {

        $this->setExpectedException(LocalizedException::class);

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $beneficiary = $this->getMockBuilder(BoletoAgent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payer = $this->getMockBuilder(BoletoAgent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this
            ->_boletoHelper
            ->expects($this->once())
            ->method('getBankCode')
            ->will($this->returnValue($bankCode));

        $this->createBoletoObjectMethod->invoke($this->subject, $order, $beneficiary, $payer);
    }



    protected function _getBoletoBradesco()
    {
        $date = date('d/m/Y');

        $boletoBradesco = <<<boleto_bradesco
<!DOCTYPE html>
<!--
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gabrielqs Tecnologia Design e Comunicação LTDA</title>
    <style type="text/css">
        /* Embed the CSS content here */
        /**
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

@media print {
    .noprint {
        display:none;
    }
}

body{
    background-color: #ffffff;
    margin-right: 0;
}

.table-boleto {
    font: 9px Arial;
    width: 666px;
}

.table-boleto td {
    border-left: 1px solid #000;
    border-top: 1px solid #000;
}

.table-boleto td:last-child {
    border-right: 1px solid #000;
}

.table-boleto .titulo {
    color: #000033;
}

.linha-pontilhada {
    color: #000033;
    font: 9px Arial;
    width: 100%;
    border-bottom: 1px dashed #000;
    text-align: right;
    margin-bottom: 10px;
}

.table-boleto .conteudo {
    font: bold 10px Arial;
    height: 13px;
}

.table-boleto .sacador {
    display: inline;
    margin-left: 5px;
}

.table-boleto td {
    padding: 1px 4px;
}

.table-boleto .noleftborder {
    border-left: none !important;
}

.table-boleto .notopborder {
    border-top: none !important;
}

.table-boleto .norightborder {
    border-right: none !important;
}

.table-boleto .noborder {
    border: none !important;
}

.table-boleto .bottomborder {
    border-bottom: 1px solid #000 !important;
}

.table-boleto .rtl {
    text-align: right;
}

.table-boleto .logobanco {
    display: inline-block;
    max-width: 150px;
}

.table-boleto .logocontainer {
    width: 257px;
    display: inline-block;
}

.table-boleto .logobanco img {
    margin-bottom: -5px;
}

.table-boleto .codbanco {
    font: bold 20px Arial;
    padding: 1px 5px;
    display: inline;
    border-left: 2px solid #000;
    border-right: 2px solid #000;
    width: 51px;
    margin-left: 25px;
}

.table-boleto .linha-digitavel {
    font: bold 14px Arial;
    display: inline-block;
    width: 406px;
    text-align: right;
}

.table-boleto .nopadding {
    padding: 0px !important;
}

.info {
    font: 11px Arial;
}

.info-empresa {
    font: 11px Arial;
}

.header {
    font: bold 13px Arial;
    display: block;
    margin: 4px;
}

.barcode {
    height: 50px;
}

.barcode div {
    display: inline-block;
    height: 100%;
}

.barcode .black {
    border-color: #000;
    border-left-style: solid;
    width: 0px;
}

.barcode .white {
    background: #fff;
}

.barcode .thin.black {
    border-left-width: 1px;
}

.barcode .large.black {
    border-left-width: 3px;
}

.barcode .thin.white {
    width: 1px;
}

.barcode .large.white {
    width: 3px;
}    </style>
</head>
<body>

    <div style="width: 666px">
        <div class="noprint info">
            <h2>Instruções de Impressão</h2>
            <ul>
                <li>Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).</li>
                <li>Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.</li>
                <li>Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.</li>
                <li>Caso não apareça o código de barras no final, pressione F5 para atualizar esta tela.</li>
                <li>Caso tenha problemas ao imprimir, copie a sequencia numérica abaixo e pague no caixa eletrônico ou no internet banking:</li>
            </ul>
            <span class="header">Linha Digitável: 23790.39205 90000.000019 25000.393808 1 69130000018590</span>
            <span class="header">Valor: R$ 185,90</span>                        <br>
            <div class="linha-pontilhada" style="margin-bottom: 20px;">Recibo do sacado</div>
        </div>

        <div class="info-empresa">
                        <div style="display: inline-block; vertical-align: super;">
                <div><strong>Gabrielqs Tecnologia Design e Comunicação LTDA</strong></div>
                <div>21.039.394/0001-21</div>
                <div>CLN 213 Bloco C Sala 204, Asa Norte</div>
                <div>70846-030 - Brasília - DF</div>
            </div>
        </div>
        <br>

        <table class="table-boleto" cellpadding="0" cellspacing="0" border="0">
            <tbody>
            <tr>
                <td valign="bottom" colspan="8" class="noborder nopadding">
                    <div class="logocontainer">
                        <div class="logobanco">
                            <img src="data:image/jpg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgMCAgMDAwMEAwMEBQgFBQQEBQoHBwYIDAoMDAsKCwsNDhIQDQ4RDgsLEBYQERMUFRUVDA8XGBYUGBIUFRT/2wBDAQMEBAUEBQkFBQkUDQsNFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBQUFBT/wAARCAAoAJYDAREAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9KPizrV94c+FfjLVtMuPsmpWGi3t1a3GxX8qVIHZG2sCpwwBwQQcciufEylChOcXZpP8AI9TKqNPEZhh6NVXjKcU13Tkk1prsfmneftqfGizhaR/Hrqo/6hFif0EFfDPNsWt5/gv8j+iocHZFN8qw3/k8/wD5I9K0T4hftRXugReIfEfxE0b4c+HpiPJvPFlrY20koOOVhFuW6n+LbXpQnmUkqlSqoRfdR/yPm6+C4Rp1nhcLhJ4iot1Tc2l8+dL7rmdqHxy+N7zrB4a+PfgzxheMSosbKGxt7iRueI1ltwrk47N/jSlWxjdqWIUvlFP8janlGQKPNjMrq0V3bqSivVqV19xxk37YPx1tJ5Le78az2l1ExSWGbRrFWRgeQR5Fec8yxydpTafpH/I9uPCfD04qcMOmns1Of/yR9WfsK/GXxr8X9L8aSeMtc/tuXT7u3jtX+yQQeWrIxYfukUHJA65r6TKcVVxUJuq72fl28rH5bxrlOBymtQjgafIpRbesnd3/ALzZ9S17p+bBQAUAFABQAUAFABQAUAFABQAUAFAHEfHJS/wU+IChlUt4e1AZY4A/0aTrXLi1fD1F/df5Hs5K7ZnhX/08h/6Uj85fgdovgrwdo+v/ABh8Tyv4qsfCU0VrY6WLTy4ZtRkwUCl8lyn3slQBkHHFfH4KlQoxli6j5lH8/wDgH77ndfMcbVpZHhY+ylWTcpc13yK+9tEn2u306nKeM/iV4F+J3iSfX/GfhXxt4g1C4Yt9ol1+FTEhPyxxxiEKijoFH4knJMTxWHrzc6sJN+v6WPSwWV5pllFYfA4ilCK6Km9X3bcm233/ACWhRh8MfAfxKjWy69408A3bD91JrllBqNmrdsmFQ4Uep9ewrFU8BUbalKD89V+B1SxXEmGfO6VKvHryScJf+TO1/Q0PiD8CfGngPw/F4mhvbLx/4GEY8vxLok/nRxRjoJU5ZAMn1AxyR2K2Bq0qftFLnh3Wphl+e4DHV3hJQdCvfWEla78ns39zfZn1B/wTImSfQ/iI8bBlN9acg5/5ZvXuZDrTqPz/AEPzTxHTjicMn/K/zPqb4u/GDwt8DPBVx4s8Y376bocEsUElxHA8xDyMFQbUBJySB0r6ZuyufkCV9DxHRP8Agpl+znrmqx6enxAjspnbbv1CxuLeJT/tO6BV/E1Cmm9n9xbg0r3X3n09BPHdQRzQyLLDIodJEOVZSMgg9wRWhmSUAFAFTVdTg0XS7zULpittaQvPKyjJCKpY8d+BQB5/8CP2ifA37Sfhm91/wFqc+qaXZ3Rsppp7OW2IlCqxAEiqTwy8jjmgD0ugAoAKACgAoAKAOH+Ooz8EfiEP+pe1D/0mkrkxn+7VP8L/ACPayT/kaYX/AK+Q/wDSkflffXUbfssWOnQsPMg8fSyXYx13WQMbH2wCB/uV8K23geVae/8Ap+p/TEIv/WGVWXXDq3ynr/n8zj5JFhRnZgqjkk9vrXmtntJX0RFKqu3kz280RZcqtxA8YYeo3AZ/Craa+Jbji/tQknbs0/y2PQ/2cviXq3wk+LOhWlgzXXhvxFfRaZq2iyEtDOkzrFv2dN67sg9+Qa78vxEsPXjBaxk0mj53iPLaGa5dVqVdKlKLlGXVcqbtfs/w3PtT9jPwnpPgb4ifHbQdC40mx8QQR26A5EamJmKD2UkqPYCvrcBTjSnWhDZSPxnivF18bhcuxGId5um7vv71r+r3ZS/4Kgor/sm6mG6f2xph/K6Q167ulofnkUm9T4D+I2u3Pi39mP4YeFb74Xp4P0eKO1jf4o6hahorxNu0ybo0LFHBydzZIAAznIT5nZdAXLbzPe/ij8a/ip8LvjH8LPhx8G/GEHiXRdQ8H2Fpo9pOkL2t9cukqLdtKQX2BEEm0NjCfgRe89GNvlVmtTvvifcfF74RfCPwvpHj/wDai8O+AvEst5dXF5r1zYR3E99EQvlRQw7FwqfPkgen0odk7XBXtscb+zD+2f4z8P8AxL8ceEvFXxAsPjJ4Y07w9feINP8AE9laC3dzbJG7R4UAYO8qQclWXqQeB+6nYS1auY/wk8cftUfHbwF40+LVn430l/DVuby3bwddWaPBcosf72OEqoZdgJUMz/MynOBSs29GO6W5w/wD/aGvv2Yf2Hde1LwdFFNr+u+KTpukSXKeZHERZxyPKRkBiI0O0E4LEZ4pa30HZWudBo37Xnjj4H+M/h3qup/tGeHvjVpXiC+hsNe8Ow2sEL6UsrLulV0Ofk3HkgD5cFecq9GuaLB72aNP4u/tRfEPxT+0z428G33x0tP2f9H0WcwaUt3pKTx3qcbXd36bgQ4fIXDYAJBNL1DyR6N+0h+0j8WfAWgfCD4V+CfEem6/8QvFdhE154yt0jaO4ZnEaNApGxd53uXKsFWM4Viaa3SuFr3bRZ8b+Ofjl+xB8GfFPif4i/Emw+Juqaq9rYeH0msVgj0+5IlaaSQKFLoEUMBkZK44zmm1bZk3vuee+MvEH7WPwV+D+hfHDVfiva65Y3T2txeeD7rS4hFbR3DKERnRcsAWVTtwV3Z3NtORrqmCdt0foL8GfiTbfGL4T+E/G9pbvZwa9psN+LeQgtEXUFkJHXByPwqiSP45/wDJE/iD/wBi9qH/AKTSVyYv/d6n+F/ke1kn/I0wv/XyH/pSPyQ0rU/selazpVwpm03VEieSMfejnibdDKue4+ZWHdWxngV+d06nLGUHs/6R/U9ajz1KdeGk4X+cZK0k/wA12ZnyeEdU8WeGPFl1pogEHh+yjv78zybW8ppAgCDB3NnscDHeinQlWjKS2jqzd46jgq9CFa96snGNu6XXsvvPp7/goeBD4g+EYjAQNpFzkKMZ5gr3s70dL5n5p4fO9LHX/mj/AO3Hzx4S8Rr4L1yLX7WzW816zydNe4wYLSUjHnsvV3UHKLwueSeBXh0KqoPnSu+nZf8ABP0DG4WWOpPDTly05fFb4pL+VPZJ9XvbRdT7G/4JmGaTS/iZPczyXV1capbzTTynLSSMkhZifUnJP1r6jI5OcKjk9b/ofkXiMoxr4SMFZKDSS6JPRHrX7cfwd8T/AB1+AGoeFPCEVtPrcuoWVzGl5ceRGUimV3y+DjgHsa+ltzaH5DGXK7nyRq/7K37V/jz4PeHPg5qg8DaP4A0tLWJZUuZJLhVgYNGXYKTJtIBwAm4gZI5zKjK/vMfNG1o7no0H7EvjTwl+038Ctc0V7PUPAngHRLTTLm+ubkJdSvFFcozCILggmVT17njiqUbXaJbbsjS/av8A2Yfidr37UPhH40/DrT/D/ii40nTksDo3iCTYkboZjvGRhlYT9iCrKG+boFy3d1uCk1p0MPwN+yN8WvEH7UGsePfiXB4eTSPEHhy80m//ALAnZBbm4t44xFHGRlggTaZCcscnAHS17uoPVnMfDT9lX9qP4WeHvEnwq0HXvDFt4D1eWYz6/OWkuRG8e1mgQFTHJIAAVIYKSWDdjHL3Y7+RL4P/AOCdfjPxB+yhrvw+8SzaX4b8VW/iD+2dBuLac3Vug+zpEUkKhCu9N6ZX7vysM4xRy9wUtEdl8OPhV+0D/bvhbRPFPwq+EEOhWVzbpquurarNc3FqpHmmJNoHmso+8QBnnAzw7ysrsPd10G/tH/Cv9ob4wXXizws3w8+HereHLi5dNC8RTzeXqFnbZzHyQdrgZG4DvjB607PuK/kZPjn/AIJ4+Mrf4TfCWTwh4ls4/iN4CgZdzlo7a4LT+eqxPglPLkyF3AqysVYc5EtJoadnc6DX/gJ8ff2qfhd4o8I/Gs+FNA8lLa68O3OiRs3+moXDm5Te+Y2RtpCkcOSMHGHy66sG1bRHF+Kv2dv2q/i18MdD+D3iWfwjpnhDTngjm12OeSW4uIoP9SWAOXwVQlcIWIGTjIMpSW7BtPZH3Z8Kvh3p3wk+G/hvwZpDSPpuh2MVjC8py7hFA3H3JyfxqiRPizot94j+FnjLSdMg+1alf6Le2trBvVPMleB1RdzEKMsQMkgDPNc+JhKpQnCO7TX4HqZXWp4fH4etVdoxnFt9kpJt6a7H5vH9jH4z8f8AFEPn/sK2P/x+vhf7Lxn/AD7/ABX+Z/Q64wyL/oI/8ln/APInWeAv2Svi1pPgn4tWF94SNtd65okNnp0R1K0bz5Vm3FcrKQvHOWIHvXpYXAYmnTqxnC11pqv8zw8z4oyevicBUpV7qnNyl7s9FZd46/I9L/bT/Z7+IfxV1f4cXHhLw5/bEWk6dNb3xF9bweS7eVgfvJF3fcb7uRxXXmuDr4n2fso3t5o+f4Nz3Lcrp4uONq8nO04+7J3tfsnbfqeDt+xj8Z+f+KIc/wDcUsf/AI/Xh/2XjP5PxX+Z95/rhkX/AEEf+Sz/APkT6p/YW+DXjT4QaX40i8Y6IdFk1G7t5bVTdQTmRVRgx/dOwGCRwcV9LlWGq4aE1VVm35dvI/LeNc2wObVqEsFU51GLT0as7+aR9SV7h+bBQAUAFABQAUAFABQAUAFABQAUAFAH/9k=" alt="logotipo do banco">
                        </div>
                        <div class="codbanco">237-2</div>
                    </div>
                    <div class="linha-digitavel">23790.39205 90000.000019 25000.393808 1 69130000018590</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" width="250">
                    <div class="titulo">Cedente</div>
                    <div class="conteudo">Gabrielqs Tecnologia Design e Comunicação LTDA</div>
                </td>
                <td>
                    <div class="titulo">CPF/CNPJ</div>
                    <div class="conteudo">21.039.394/0001-21</div>
                </td>
                <td width="120">
                    <div class="titulo">Agência/Código do Cedente</div>
                    <div class="conteudo rtl">0392 / 3938</div>
                </td>
                <td width="110">
                    <div class="titulo">Vencimento</div>
                    <div class="conteudo rtl">10/09/2016</div>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="titulo">Sacado</div>
                    <div class="conteudo">Roberto Marinho</div>
                </td>
                <td>
                    <div class="titulo">Nº documento</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">Nosso número</div>
                    <div class="conteudo rtl">00000125</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">Espécie</div>
                    <div class="conteudo">REAL</div>
                </td>
                <td>
                    <div class="titulo">Quantidade</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">Valor</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">(-) Descontos / Abatimentos</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">(=) Valor Documento</div>
                    <div class="conteudo rtl">185,90</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="conteudo"></div>
                    <div class="titulo">Demonstrativo</div>
                </td>
                <td>
                    <div class="titulo">(-) Outras deduções</div>
                    <div class="conteudo"></div>
                </td>
                <td>
                    <div class="titulo">(+) Outros acréscimos</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">(=) Valor cobrado</div>
                    <div class="conteudo rtl"></div>
                </td>
            </tr>
            <tr>
                <td colspan="4"><div style="margin-top: 10px" class="conteudo">Pedido efetuado na loja virtual Loja Gabrielqs V2</div></td>
                <td class="noleftborder"><div class="titulo">Autenticação mecânica</div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder bottomborder"><div style="margin-bottom: 10px;" class="conteudo"></div></td>
            </tr>
            </tbody>
        </table>
        <br>
        <div class="linha-pontilhada">Corte na linha pontilhada</div>
        <br>

        <!-- Ficha de compensação -->
        <!--
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->

<table class="table-boleto" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td valign="bottom" colspan="8" class="noborder nopadding">
            <div class="logocontainer">
                <div class="logobanco">
                    <img src="data:image/jpg;base64,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" alt="logotipo do banco">
                </div>
                <div class="codbanco">237-2</div>
            </div>
            <div class="linha-digitavel">23790.39205 90000.000019 25000.393808 1 69130000018590</div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Local de pagamento</div>
            <div class="conteudo">Pagável em qualquer agência bancária até o vencimento.</div>
        </td>
        <td width="180">
            <div class="titulo">Vencimento</div>
            <div class="conteudo rtl">10/09/2016</div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Cedente</div>
            <div class="conteudo">Gabrielqs Tecnologia Design e Comunicação LTDA</div>
        </td>
        <td>
            <div class="titulo">Agência/Código cedente</div>
            <div class="conteudo rtl">0392 / 3938</div>
        </td>
    </tr>
    <tr>
        <td width="110" colspan="2">
            <div class="titulo">Data do documento</div>
            <div class="conteudo">$date</div>
        </td>
        <td width="120" colspan="2">
            <div class="titulo">Nº documento</div>
            <div class="conteudo"></div>
        </td>
        <td width="60">
            <div class="titulo">Espécie doc.</div>
            <div class="conteudo">DM</div>
        </td>
        <td>
            <div class="titulo">Aceite</div>
            <div class="conteudo">N</div>
        </td>
        <td width="110">
            <div class="titulo">Data processamento</div>
            <div class="conteudo">$date</div>
        </td>
        <td>
            <div class="titulo">Nosso número</div>
            <div class="conteudo rtl">00000125</div>
        </td>
    </tr>
    <tr>
                    <td>
                <div class="titulo">Uso do banco</div>
                <div class="conteudo"></div>
            </td>

                    <!-- Campo exclusivo do Bradesco -->
            <td width="20">
                <div class="titulo">CIP</div>
                <div class="conteudo">000</div>
            </td>

        <td>
            <div class="titulo">Carteira</div>
            <div class="conteudo">9</div>
        </td>
        <td width="35">
            <div class="titulo">Espécie</div>
            <div class="conteudo">REAL</div>
        </td>
        <td colspan="2">
            <div class="titulo">Quantidade</div>
            <div class="conteudo"></div>
        </td>
        <td width="110">
            <div class="titulo">Valor</div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(=) Valor do Documento</div>
            <div class="conteudo rtl">185,90</div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Instruções (Texto de responsabilidade do cedente)</div>
        </td>
        <td>
            <div class="titulo">(-) Descontos / Abatimentos</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo">Sr. Caixa, não receber após o vencimento.</div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(-) Outras deduções</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"></div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(+) Mora / Multa</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"></div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(+) Outros acréscimos</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"></div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(=) Valor cobrado</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Sacado</div>
            <div class="conteudo">Roberto Marinho</div>
            <div class="conteudo">Rua Comendador Gomes, nº 172, Próximo ao banco, Centro</div>
            <div class="conteudo">01372-010 - São Paulo - SP</div>

        </td>
        <td class="noleftborder">
            <div class="titulo" style="margin-top: 50px">Cód. Baixa</div>
        </td>
    </tr>

    <tr>
        <td colspan="6" class="noleftborder">
            <div class="titulo">Sacador/Avalista <div class="conteudo sacador"></div></div>
        </td>
        <td colspan="2" class="norightborder noleftborder">
            <div class="conteudo noborder rtl">Autenticação mecânica - Ficha de Compensação</div>
        </td>
    </tr>

    <tr>
        <td colspan="8" class="noborder">
            <div class="barcode"><div class="black thin"></div><div class="white thin"></div><div class="black thin"></div><div class="white thin"></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class="black large"></div><div class="white thin"></div><div class="black thin"></div></div>        </td>
    </tr>

    </tbody>
</table>    </div>
</body>
</html>
boleto_bradesco;
        return trim($boletoBradesco);
    }

    protected function _getBoletoItau()
    {
        $date = date('d/m/Y');
        $boletoItau = <<<boleto_itau
<!DOCTYPE html>
<!--
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gabrielqs Tecnologia Design e Comunicação LTDA</title>
    <style type="text/css">
        /* Embed the CSS content here */
        /**
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

@media print {
    .noprint {
        display:none;
    }
}

body{
    background-color: #ffffff;
    margin-right: 0;
}

.table-boleto {
    font: 9px Arial;
    width: 666px;
}

.table-boleto td {
    border-left: 1px solid #000;
    border-top: 1px solid #000;
}

.table-boleto td:last-child {
    border-right: 1px solid #000;
}

.table-boleto .titulo {
    color: #000033;
}

.linha-pontilhada {
    color: #000033;
    font: 9px Arial;
    width: 100%;
    border-bottom: 1px dashed #000;
    text-align: right;
    margin-bottom: 10px;
}

.table-boleto .conteudo {
    font: bold 10px Arial;
    height: 13px;
}

.table-boleto .sacador {
    display: inline;
    margin-left: 5px;
}

.table-boleto td {
    padding: 1px 4px;
}

.table-boleto .noleftborder {
    border-left: none !important;
}

.table-boleto .notopborder {
    border-top: none !important;
}

.table-boleto .norightborder {
    border-right: none !important;
}

.table-boleto .noborder {
    border: none !important;
}

.table-boleto .bottomborder {
    border-bottom: 1px solid #000 !important;
}

.table-boleto .rtl {
    text-align: right;
}

.table-boleto .logobanco {
    display: inline-block;
    max-width: 150px;
}

.table-boleto .logocontainer {
    width: 257px;
    display: inline-block;
}

.table-boleto .logobanco img {
    margin-bottom: -5px;
}

.table-boleto .codbanco {
    font: bold 20px Arial;
    padding: 1px 5px;
    display: inline;
    border-left: 2px solid #000;
    border-right: 2px solid #000;
    width: 51px;
    margin-left: 25px;
}

.table-boleto .linha-digitavel {
    font: bold 14px Arial;
    display: inline-block;
    width: 406px;
    text-align: right;
}

.table-boleto .nopadding {
    padding: 0px !important;
}

.info {
    font: 11px Arial;
}

.info-empresa {
    font: 11px Arial;
}

.header {
    font: bold 13px Arial;
    display: block;
    margin: 4px;
}

.barcode {
    height: 50px;
}

.barcode div {
    display: inline-block;
    height: 100%;
}

.barcode .black {
    border-color: #000;
    border-left-style: solid;
    width: 0px;
}

.barcode .white {
    background: #fff;
}

.barcode .thin.black {
    border-left-width: 1px;
}

.barcode .large.black {
    border-left-width: 3px;
}

.barcode .thin.white {
    width: 1px;
}

.barcode .large.white {
    width: 3px;
}    </style>
</head>
<body>

    <div style="width: 666px">
        <div class="noprint info">
            <h2>Instruções de Impressão</h2>
            <ul>
                <li>Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).</li>
                <li>Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.</li>
                <li>Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.</li>
                <li>Caso não apareça o código de barras no final, pressione F5 para atualizar esta tela.</li>
                <li>Caso tenha problemas ao imprimir, copie a sequencia numérica abaixo e pague no caixa eletrônico ou no internet banking:</li>
            </ul>
            <span class="header">Linha Digitável: 34191.75009 00012.570396 20393.840002 1 69130000018590</span>
            <span class="header">Valor: R$ 185,90</span>                        <br>
            <div class="linha-pontilhada" style="margin-bottom: 20px;">Recibo do sacado</div>
        </div>

        <div class="info-empresa">
                        <div style="display: inline-block; vertical-align: super;">
                <div><strong>Gabrielqs Tecnologia Design e Comunicação LTDA</strong></div>
                <div>21.039.394/0001-21</div>
                <div>CLN 213 Bloco C Sala 204, Asa Norte</div>
                <div>70846-030 - Brasília - DF</div>
            </div>
        </div>
        <br>

        <table class="table-boleto" cellpadding="0" cellspacing="0" border="0">
            <tbody>
            <tr>
                <td valign="bottom" colspan="8" class="noborder nopadding">
                    <div class="logocontainer">
                        <div class="logobanco">
                            <img src="data:image/jpg;base64,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" alt="logotipo do banco">
                        </div>
                        <div class="codbanco">341-7</div>
                    </div>
                    <div class="linha-digitavel">34191.75009 00012.570396 20393.840002 1 69130000018590</div>
                </td>
            </tr>
            <tr>
                <td colspan="2" width="250">
                    <div class="titulo">Cedente</div>
                    <div class="conteudo">Gabrielqs Tecnologia Design e Comunicação LTDA</div>
                </td>
                <td>
                    <div class="titulo">CPF/CNPJ</div>
                    <div class="conteudo">21.039.394/0001-21</div>
                </td>
                <td width="120">
                    <div class="titulo">Agência/Código do Cedente</div>
                    <div class="conteudo rtl">0392 / 3938</div>
                </td>
                <td width="110">
                    <div class="titulo">Vencimento</div>
                    <div class="conteudo rtl">10/09/2016</div>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="titulo">Sacado</div>
                    <div class="conteudo">Roberto Marinho</div>
                </td>
                <td>
                    <div class="titulo">Nº documento</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">Nosso número</div>
                    <div class="conteudo rtl">175/00000125-7</div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="titulo">Espécie</div>
                    <div class="conteudo">REAL</div>
                </td>
                <td>
                    <div class="titulo">Quantidade</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">Valor</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">(-) Descontos / Abatimentos</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">(=) Valor Documento</div>
                    <div class="conteudo rtl">185,90</div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="conteudo"></div>
                    <div class="titulo">Demonstrativo</div>
                </td>
                <td>
                    <div class="titulo">(-) Outras deduções</div>
                    <div class="conteudo"></div>
                </td>
                <td>
                    <div class="titulo">(+) Outros acréscimos</div>
                    <div class="conteudo rtl"></div>
                </td>
                <td>
                    <div class="titulo">(=) Valor cobrado</div>
                    <div class="conteudo rtl"></div>
                </td>
            </tr>
            <tr>
                <td colspan="4"><div style="margin-top: 10px" class="conteudo">Pedido efetuado na loja virtual Loja Gabrielqs V2</div></td>
                <td class="noleftborder"><div class="titulo">Autenticação mecânica</div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder"><div class="conteudo"></div></td>
            </tr>
            <tr>
                <td colspan="5" class="notopborder bottomborder"><div style="margin-bottom: 10px;" class="conteudo"></div></td>
            </tr>
            </tbody>
        </table>
        <br>
        <div class="linha-pontilhada">Corte na linha pontilhada</div>
        <br>

        <!-- Ficha de compensação -->
        <!--
 * OpenBoleto - Geração de boletos bancários em PHP
 *
 * LICENSE: The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this
 * software and associated documentation files (the "Software"), to deal in the Software
 * without restriction, including without limitation the rights to use, copy, modify,
 * merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
-->

<table class="table-boleto" cellpadding="0" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td valign="bottom" colspan="8" class="noborder nopadding">
            <div class="logocontainer">
                <div class="logobanco">
                    <img src="data:image/jpg;base64,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" alt="logotipo do banco">
                </div>
                <div class="codbanco">341-7</div>
            </div>
            <div class="linha-digitavel">34191.75009 00012.570396 20393.840002 1 69130000018590</div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Local de pagamento</div>
            <div class="conteudo">Até o vencimento, pague preferencialmente no Itaú. Após o vencimento pague somente no Itaú</div>
        </td>
        <td width="180">
            <div class="titulo">Vencimento</div>
            <div class="conteudo rtl">10/09/2016</div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Cedente</div>
            <div class="conteudo">Gabrielqs Tecnologia Design e Comunicação LTDA</div>
        </td>
        <td>
            <div class="titulo">Agência/Código cedente</div>
            <div class="conteudo rtl">0392 / 3938</div>
        </td>
    </tr>
    <tr>
        <td width="110" colspan="2">
            <div class="titulo">Data do documento</div>
            <div class="conteudo">$date</div>
        </td>
        <td width="120" colspan="2">
            <div class="titulo">Nº documento</div>
            <div class="conteudo"></div>
        </td>
        <td width="60">
            <div class="titulo">Espécie doc.</div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">Aceite</div>
            <div class="conteudo">N</div>
        </td>
        <td width="110">
            <div class="titulo">Data processamento</div>
            <div class="conteudo">$date</div>
        </td>
        <td>
            <div class="titulo">Nosso número</div>
            <div class="conteudo rtl">175/00000125-7</div>
        </td>
    </tr>
    <tr>
                    <td colspan="2">
                <div class="titulo">Uso do banco</div>
                <div class="conteudo"></div>
            </td>


        <td>
            <div class="titulo">Carteira</div>
            <div class="conteudo"></div>
        </td>
        <td width="35">
            <div class="titulo">Espécie</div>
            <div class="conteudo">REAL</div>
        </td>
        <td colspan="2">
            <div class="titulo">Quantidade</div>
            <div class="conteudo"></div>
        </td>
        <td width="110">
            <div class="titulo">Valor</div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(=) Valor do Documento</div>
            <div class="conteudo rtl">185,90</div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Instruções (Texto de responsabilidade do cedente)</div>
        </td>
        <td>
            <div class="titulo">(-) Descontos / Abatimentos</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo">Sr. Caixa, não receber após o vencimento.</div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(-) Outras deduções</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"></div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(+) Mora / Multa</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"></div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(+) Outros acréscimos</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7" class="notopborder">
            <div class="conteudo"></div>
            <div class="conteudo"></div>
        </td>
        <td>
            <div class="titulo">(=) Valor cobrado</div>
            <div class="conteudo rtl"></div>
        </td>
    </tr>
    <tr>
        <td colspan="7">
            <div class="titulo">Sacado</div>
            <div class="conteudo">Roberto Marinho</div>
            <div class="conteudo">Rua Comendador Gomes, nº 172, Próximo ao banco, Centro</div>
            <div class="conteudo">01372-010 - São Paulo - SP</div>

        </td>
        <td class="noleftborder">
            <div class="titulo" style="margin-top: 50px">Cód. Baixa</div>
        </td>
    </tr>

    <tr>
        <td colspan="6" class="noleftborder">
            <div class="titulo">Sacador/Avalista <div class="conteudo sacador"></div></div>
        </td>
        <td colspan="2" class="norightborder noleftborder">
            <div class="conteudo noborder rtl">Autenticação mecânica - Ficha de Compensação</div>
        </td>
    </tr>

    <tr>
        <td colspan="8" class="noborder">
            <div class="barcode"><div class="black thin"></div><div class="white thin"></div><div class="black thin"></div><div class="white thin"></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white large'></div><div class='black large'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black thin'></div><div class='white thin'></div><div class='black large'></div><div class='white large'></div><div class='black large'></div><div class='white large'></div><div class='black thin'></div><div class='white thin'></div><div class="black large"></div><div class="white thin"></div><div class="black thin"></div></div>        </td>
    </tr>

    </tbody>
</table>    </div>
</body>
</html>
boleto_itau;
        return trim ($boletoItau);
    }

}