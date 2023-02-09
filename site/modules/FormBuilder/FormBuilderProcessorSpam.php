<?php namespace ProcessWire;

/**
 * ProcessWire Form Builder Processor: Spam
 *
 * Copyright (C) 2021 by Ryan Cramer Design, LLC
 *
 * PLEASE DO NOT DISTRIBUTE
 *
 */

class FormBuilderProcessorSpam extends Wire {

	/**
	 * @var FormBuilderProcessor
	 *
	 */
	protected $processor;

	/**
	 * Construct
	 *
	 * @param FormBuilderProcessor $processor
	 *
	 */
	public function __construct(FormBuilderProcessor $processor) {
		$this->processor = $processor;
	}

	/**
	 * Does given processed form contain spam?
	 *
	 * - Returns name of spam filter that was triggered if yes.
	 * - Returns blank string if no.
	 *
	 * Note: form will fail silently if spam is detected, unless spam filter adds $form->error().
	 *
	 * @param InputfieldForm $form
	 * @return bool string
	 * @since 0.4.7
	 *
	 */
	public function isSpam(InputfieldForm $form) {
		// perform optional turing test
		if($this->processor->turingTest) {
			if($this->turingTest($form, $this->processor->turingTest)) {
				return 'turingTest';
			}
		}
		if(is_array($this->processor->spamWords) && count($this->processor->spamWords)) {
			if($this->spamWords($form, $this->processor->spamWords)) {
				return 'keywords';
			}
		}
		// perform optional Akismet spam filtering
		if($this->processor->akismet && !count($form->getErrors())) {
			if($this->akismet($form, $this->processor->akismet)) {
				return 'akismet';
			}
		}
		return '';
	}

	/**
	 * Check the submission against a turing test, when enabled
	 *
	 * @param InputfieldForm $form
	 * @param array $turingTests
	 * @return bool True if spam, false if not
	 *
	 */
	public function turingTest(InputfieldForm $form, $turingTests) {
		$isSpam = false;
		foreach($turingTests as $fieldName => $answer) {
			$field = $form->getChildByName($fieldName);
			if(!$field || !$field instanceof Inputfield) continue;
			if(strtolower($field->attr('value')) != strtolower($answer)) {
				$field->error($this->_('Incorrect answer'));
				$isSpam = true;
			}
		}
		return $isSpam;
	}

	/**
	 * Check the submission against Akismet, when enabled
	 *
	 * Akismet check is not performed if other errors have already occurred.
	 *
	 * @param InputfieldForm $form
	 * @param string $akismet
	 * @return bool Returns true if spam, false if not
	 *
	 */
	public function akismet(InputfieldForm $form, $akismet) {

		$parts = explode(',', $akismet);
		while(count($parts) < 3) $parts[] = '';
		list($author, $email, $content) = $parts;

		$author = $form->getChildByName($author);
		$author = $author ? $author->attr('value') : '';

		$email = $form->getChildByName($email);
		$email = $email ? $email->attr('value') : '';

		$content = $form->getChildByName($content);
		$content = $content ? $content->attr('value') : '';

		require_once(dirname(__FILE__) . '/FormBuilderAkismet.php');

		/** @var FormBuilder $forms */
		$forms = $this->wire()->forms;
		$akismet = new FormBuilderAkismet($forms->akismetKey);

		if($akismet->isSpam($author, $email, $content)) {
			if($this->wire()->config->debug) {
				$this->processor->addError($this->_('Spam filter has been triggered'));
			} else {
				$this->processor->addError($this->_('Unable to process form submission'));
			}
			return true;
		}

		return false;
	}

	/**
	 * Check the submission against Akismet, when enabled
	 *
	 * Akismet check is not performed if other errors have already occurred.
	 *
	 * @param InputfieldForm $form
	 * @param array $spamWords
	 * @return bool True if spam, false if not
	 *
	 */
	public function spamWords(InputfieldForm $form, $spamWords) {

		if(!is_array($spamWords) || !count($spamWords)) return false;
		
		$isSpam = false;
		$operators = array('*=', '~=', '%=', '^=', '$=', '='); // note the '=' operator must be last

		// first check 'field_name=keyword' versions
		foreach($spamWords as $key => $spamWord) {

			$operator = '%=';
			$spamWord = trim($spamWord);

			if(!strlen($spamWord)) unset($spamWords[$key]);
			if(strpos($spamWord, '=') === false) continue;

			while(strpos($spamWord, '  ')) {
				$spamWord = str_replace('  ', ' ', $spamWord);
			}

			foreach($operators as $op) {
				if(strpos($spamWord, $op) !== false) {
					$operator = $op;
					break;
				}
			}

			if(strpos($spamWord, $operator) !== false) {
				list($fieldName, $spamWord) = explode($operator, $spamWord, 2);
			} else {
				$fieldName = '';
			}

			$fieldName = trim($fieldName);
			$spamWord = trim($spamWord);
			$inputfield = null;
			$values = array();
			$not = false;

			if($fieldName && strpos($fieldName, '!') !== false) {
				$not = true;
				$fieldName = trim($fieldName, '!');
			}

			if(strlen($fieldName)) {
				if(isset($_POST[$fieldName])) {
					$value = $_POST[$fieldName];
				} else {
					$inputfield = $form->getChildByName(trim($fieldName));
					$value = $inputfield ? $inputfield->val() : '';
				}
				if(is_array($value)) {
					$values = $value;
				} else {
					$values[] = trim($value);
				}
			} else {
				$values = $_POST;
			}

			foreach($values as $value) {
				$value = trim($value);
				switch($operator) {
					case '*=':
					case '~=':
						$spamWord = preg_quote($spamWord);
						$spamWord = str_replace(' ', '\s+', $spamWord);
						$re = '/\b' . $spamWord . ($operator === '~=' ? '\b' : '') . '/is';
						$isSpam = preg_match($re, $value);
						break;
					case '%=':
						$isSpam = stripos($value, $spamWord) !== false;
						break;
					case '=':
						$isSpam = strtolower($value) === strtolower($spamWord);
						break;
					case '^=':
						$isSpam = stripos($value, $spamWord) === 0;
						break;
					case '$=':
						$value = substr($value, -1 * strlen($spamWord));
						$isSpam = strtolower($value) === strtolower($spamWord);
						break;
					default:
						$isSpam = stripos($value, $spamWord) !== false;
				}
				if($not) $isSpam = !$isSpam;
				if($isSpam) break;
			}
			unset($spamWords[$key]);
			if($isSpam) break;
		}

		if(!$isSpam) {
			// next check for keywords that appear anywhere in POST data
			foreach($spamWords as $spamWord) {
				foreach($_POST as $key => $value) {
					$isSpam = stripos($value, $spamWord) !== false;
					if($isSpam) break;
				}
			}
		}

		return $isSpam;
	}

}
