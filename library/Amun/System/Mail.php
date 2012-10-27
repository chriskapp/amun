<?php

class Amun_System_Mail extends Amun_Data_RecordAbstract
{
	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Amun_Filter_Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setName($name)
	{
		$name = $this->_validate->apply($name, 'string', array(new PSX_Filter_Length(3, 32)), 'name', 'Name');

		if(!$this->_validate->hasError())
		{
			$this->name = strtoupper($name);
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setFrom($from)
	{
		$from = $this->_validate->apply($from, 'string', array(new PSX_Filter_Length(3, 64), new PSX_Filter_Email()), 'from', 'From');

		if(!$this->_validate->hasError())
		{
			$this->from = $from;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setSubject($subject)
	{
		$subject = $this->_validate->apply($subject, 'string', array(new PSX_Filter_Length(3, 256)), 'subject', 'Subject');

		if(!$this->_validate->hasError())
		{
			$this->subject = $subject;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setText($text)
	{
		$text = $this->_validate->apply($text, 'string', array(new PSX_Filter_Length(3, 4096)), 'text', 'Text');

		if(!$this->_validate->hasError())
		{
			$this->text = $text;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setHtml($html)
	{
		$html = $this->_validate->apply($html, 'string', array(new PSX_Filter_Length(3, 4096)), 'html', 'Html');

		if(!$this->_validate->hasError())
		{
			$this->html = $html;
		}
		else
		{
			throw new PSX_Data_Exception($this->_validate->getLastError());
		}
	}

	public function setValues($values)
	{
		$data  = array();
		$parts = explode(';', $values);

		foreach($parts as $part)
		{
			$part = trim($part);

			if(!empty($part))
			{
				$data[] = $part;
			}
		}

		$this->values = implode(';', $data);
	}
}

