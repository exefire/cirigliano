<?php

/**
 * This is the model class for table "transporte".
 *
 * The followings are the available columns in table 'transporte':
 * @property integer $id
 * @property integer $id_usuario
 * @property integer $id_punto
 * @property string $fecha_ingreso
 * @property string $fecha_ejecucion
 * @property integer $id_estado_transporte
 * @property integer $id_autorizado
 * @property string $observaciones
 *
 * The followings are the available model relations:
 * @property DetalleTransporte[] $detalleTransportes
 * @property FotoTransporte[] $fotoTransportes
 * @property FotoSolicitudTransporte[] $fotoSolicitudTransportes
 * @property Punto $idPunto
 * @property EstadoTransporte $idEstadoTransporte
 * @property Usuario $idUsuario
 */
class Transporte extends MyActiveRecord
{
	public $punto_direccion;
	public $punto_region_id;
	public $punto_comuna_id;
	public $punto_distribuidor_id;
	public $punto_canal_id;
	public $fecha_creacion_inicio;
    public $fecha_creacion_final;
    public $fecha_visita_inicio;
    public $fecha_visita_final;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'transporte';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_usuario, id_punto, id_estado_transporte, id_autorizado', 'numerical', 'integerOnly'=>true),
			array('fecha_ingreso, fecha_ejecucion, observaciones', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, id_usuario, id_punto, fecha_ingreso, fecha_ejecucion, id_estado_transporte, id_autorizado, observaciones', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'detalleTransportes' => array(self::HAS_MANY, 'DetalleTransporte', 'id_transporte'),
			'fotoTransportes' => array(self::HAS_MANY, 'FotoTransporte', 'id_transporte'),
			'fotoSolicitudTransportes' => array(self::HAS_MANY, 'FotoSolicitudTransporte', 'id_transporte'),
			'idPunto' => array(self::BELONGS_TO, 'Punto', 'id_punto'),
			'idEstadoTransporte' => array(self::BELONGS_TO, 'EstadoTransporte', 'id_estado_transporte'),
			'idUsuario' => array(self::BELONGS_TO, 'Usuario', 'id_usuario'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'id_usuario' => 'Usuario',
			'id_punto' => 'Punto',
			'fecha_ingreso' => 'Fecha Ingreso',
			'fecha_ejecucion' => 'Fecha Ejecucion',
			'id_estado_transporte' => 'Estado',
			'id_autorizado' => 'Autorizado',
			'observaciones' => 'Observaciones',
			'punto_comuna_id'=> 'Comuna',
			'punto_direccion' => 'Dirección',
			'punto_canal_id' =>'Canal',
			'punto_distribuidor_id' => 'Distribuidor',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('id_usuario',$this->id_usuario);
		$criteria->compare('id_punto',$this->id_punto);
		$criteria->compare('fecha_ingreso',$this->fecha_ingreso,true);
		$criteria->compare('fecha_ejecucion',$this->fecha_ejecucion,true);
		$criteria->compare('id_estado_transporte',$this->id_estado_transporte);
		$criteria->compare('id_autorizado',$this->id_autorizado);
		$criteria->compare('observaciones',$this->observaciones,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	public function searchRelations($params = array())
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		if(!empty($params)){
		}


		$criteria->with = array('idPunto'=>array('alias'=>'idPunto','select'=>'*'));
		$criteria->together= true;
		if((isset($this->fecha_creacion_inicio) && trim($this->fecha_creacion_inicio) != "") && (isset($this->fecha_creacion_final) && trim($this->fecha_creacion_final) != ""))
                        $criteria->addBetweenCondition('t.fecha_ingreso', ''.date('Y-m-d',strtotime($this->fecha_creacion_inicio)).'', ''.date('Y-m-d',strtotime($this->fecha_creacion_final)).'');
        if((isset($this->fecha_visita_inicio) && trim($this->fecha_visita_inicio) != "") && (isset($this->fecha_visita_final) && trim($this->fecha_visita_final) != ""))
                        $criteria->addBetweenCondition('t.fecha_ejecucion', ''.date('Y-m-d',strtotime($this->fecha_creacion_inicio)).'', ''.date('Y-m-d',strtotime($this->fecha_visita_final)).'');
		$criteria->compare('"idPunto".direccion',$this->punto_direccion,true);
		$criteria->compare('"idPunto".id_comuna',$this->punto_comuna_id);
		$criteria->compare('"idPunto".id_distribuidor',$this->punto_distribuidor_id);
		$criteria->compare('"idPunto".id_canal',$this->punto_canal_id);
		$criteria->compare('t.id_estado_transporte',$this->id_estado_transporte);
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * @return CDbConnection the database connection used for this class
	 */
	public function getDbConnection()
	{
		return Yii::app()->db2;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your MyActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Transporte the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
