<?php $neto = 0;
$medios = array('1'=>'Camioneta, 3,5Mts3','2'=>'Camión ¾, 6,5Mts3','3'=>'Camión, 30Mts3','4'=>'Carro ffvv terreno');
?>
<div class="row">
    <div style=" height:200px; overflow:auto;  ">
        <table class="table table-condensed table-bordered table-striped">
            <tr>
                <th>Folio</th>
                <th>Punto Origen</th>
                <th>Punto Destino</th>
                <th>Fecha</th>
                <th>Detalle</th>
                <th>Medio</th>
                <th>Total</th>
            </tr>
            <?php foreach ($presupuestos as $presupuesto): ?>
                <?php $citems = count($presupuesto->trasladopresupuesto)+1; ?>
                <?php $neto += $presupuesto->TTraslado;?>
                <?php if ($presupuesto->visita->tipo_visita_id == 3): ?>
                    <tr>
                        <td rowspan="<?php echo $citems ?>" style="vertical-align:middle"><?php echo $presupuesto->visita->folio; ?></td>
                        <td rowspan="<?php echo $citems ?>" style="vertical-align:middle"><?php echo $presupuesto->visita->punto->Descripcion; ?></td>
                        <td rowspan="<?php echo $citems ?>" style="vertical-align:middle"><?php echo $presupuesto->visita->destino->Descripcion; ?></td>
                        <td rowspan="<?php echo $citems ?>" style="vertical-align:middle"><?php echo date('d-m-Y',strtotime($presupuesto->visita->fecha_visita)); ?></td>
                        <td><?php echo $presupuesto->tarifaTraslado->Descripcion; ?></td>
                        <td><?php echo $medios[$presupuesto->tipo_tarifa_traslado]; ?></td>
                        <td><?php echo Yii::app()->numberFormatter->format('###,###,###,###',$presupuesto->TTraslado); ?></td>   
                    </tr>
                    <?php foreach ($presupuesto->trasladopresupuesto as $traslado): ?>
                            <tr>
                                <td colspan="2"><?php echo 'Instalación '.$traslado->mueblePunto->Descripcion; ?></td>
                                <td><?php echo Yii::app()->numberFormatter->format('###,###,###,###',$traslado->tarifa_instalacion); ?></td>
                            </tr>
                    <?php $neto += $traslado->tarifa_instalacion;?>
                    <?php endforeach ?> 
                <?php endif ?>
            <?php endforeach ?>
        </table>
    </div>
</div>
<br>
<div class="row well">
        <p><b>Neto: </b><?php echo Yii::app()->numberFormatter->format('###,###,###,###',$neto); ?></p>
        <p><b>Iva: </b><?php echo Yii::app()->numberFormatter->format('###,###,###,###',$neto*0.19); ?></p>
        <p><b>Total: </b><?php echo Yii::app()->numberFormatter->format('###,###,###,###',$neto*1.19); ?></p>
</div>