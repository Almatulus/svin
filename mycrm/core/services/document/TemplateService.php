<?php

namespace core\services\document;

use core\forms\document\TemplateForm;
use core\models\document\DocumentTemplate;
use core\repositories\user\UserRepository;

class TemplateService
{
    /** @var UserRepository */
    protected $userRepository;

    /**
     * TemplateService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param TemplateForm $form
     * @param int $user_id
     * @return DocumentTemplate
     */
    public function create(TemplateForm $form, int $user_id): DocumentTemplate
    {
        $user = $this->userRepository->find($user_id);

        $template = new DocumentTemplate([
            'name'             => $form->name,
            'document_form_id' => $form->getId(),
            'values'           => $this->getValues($form),
            'created_by'       => $user->id
        ]);

        $template->save(false);
        $template->refresh();

        return $template;
    }

    /**
     * @param TemplateForm $form
     * @return string
     */
    private function getValues(TemplateForm $form)
    {
        $values = array_filter($form->attributes, function ($value) {
            return !is_null($value);
        });

        if (isset($values['name'])) {
            unset($values['name']);
        }

        return json_encode($values);
    }

    /**
     * @param int $id
     * @param TemplateForm $form
     * @return DocumentTemplate
     */
    public function update(int $id, TemplateForm $form): DocumentTemplate
    {
        $template = DocumentTemplate::findOne($id);

        if (!$template) {
            throw new \InvalidArgumentException("Invalid template id");
        }

        $template->setAttributes([
            'name'   => $form->name,
            'values' => $this->getValues($form)
        ]);
        $template->save(false);
        $template->refresh();

        return $template;
    }
}