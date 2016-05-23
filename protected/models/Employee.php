<?php

/**
 * This is the model class for table "employee".
 *
 * The followings are the available columns in table 'employee':
 * @property integer $id
 * @property integer $departmentid
 * @property string $firstname
 * @property string $lastname
 * @property string $email
 * @property integer $ext
 * @property string $hiredate
 * @property string $leavedate
 *
 * The followings are the available model relations:
 * @property Department $department
 */
class Employee extends CActiveRecord
{


	public $department_name;

	public $tid;
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'employee';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('departmentid, firstname, lastname, email', 'required'),
			array('id, departmentid, ext', 'numerical', 'integerOnly'=>true),
			array('hiredate, leavedate', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, departmentid, firstname, lastname, email, ext, hiredate, leavedate, department_name', 'safe', 'on'=>'search'),
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
			'department' => array(self::BELONGS_TO, 'Department', 'departmentid'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'departmentid' => 'Departmentid',
			'firstname' => 'Firstname',
			'lastname' => 'Lastname',
			'email' => 'Email',
			'ext' => 'Ext',
			'hiredate' => 'Hiredate',
			'leavedate' => 'Leavedate',
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

		$criteria->together = true;

		$criteria->with = array( 'department' );

		$criteria->compare( 'department.name', $this->department_name, true );
		$criteria->compare('t.id',$this->id);
		$criteria->compare('departmentid',$this->departmentid);
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('email',$this->email,true);

		$criteria->compare('ext',$this->ext);
		$criteria->compare('hiredate',$this->hiredate,true);
		$criteria->compare('leavedate',$this->leavedate,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'attributes'=>array(
					'department_name'=>array(
						'asc'=>'department.name',
						'desc'=>'department.name DESC',
					)
		))));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Employee the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
