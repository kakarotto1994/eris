<?php 


namespace Hcode\Model;
use \Hcode\DB\Sql;

use \Hcode\Model\Order;
use \Hcode\Model;

class OrderStatus extends Model {

    const  STATUS_EM_ABERTO = 1;
    const STATUS_AGUARDANDO_PAGAMENTO = 2;
    const STATUS_PAGO = 3;
    const STATUS_ENTREGUE = 4;
    const STATUS_CANCELADO = 5;

}




?>