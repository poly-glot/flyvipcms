<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AppModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

abstract class CrudController extends BaseController
{
    abstract protected function slug(): string;

    abstract protected function title(): string;

    abstract protected function model(): AppModel;

    abstract protected function fields(): array;

    public function index(): string
    {
        return view('admin/crud/index', $this->viewData() + [
            'rows' => $this->model()->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    public function new(): string
    {
        return view('admin/crud/form', $this->viewData() + ['row' => [], 'action' => "admin/{$this->slug()}"]);
    }

    public function create(): RedirectResponse
    {
        $data = $this->validated();

        if ($data === null) {
            return $this->backWithErrors($this->validator?->getErrors() ?? []);
        }

        $this->model()->insert($data);

        return $this->back("admin/{$this->slug()}", 'success', "{$this->title()} created.");
    }

    public function edit(int $id): string
    {
        return view('admin/crud/form', $this->viewData() + ['row' => $this->find($id), 'action' => "admin/{$this->slug()}/{$id}"]);
    }

    public function update(int $id): RedirectResponse
    {
        $this->find($id);
        $data = $this->validated();

        if ($data === null) {
            return $this->backWithErrors($this->validator?->getErrors() ?? []);
        }

        $this->model()->update($id, $data);

        return $this->back("admin/{$this->slug()}", 'success', "{$this->title()} updated.");
    }

    public function delete(int $id): RedirectResponse
    {
        $this->find($id);
        $this->model()->delete($id);

        return $this->back("admin/{$this->slug()}", 'success', "{$this->title()} deleted.");
    }

    private function viewData(): array
    {
        return ['slug' => $this->slug(), 'title' => $this->title(), 'fields' => $this->fields()];
    }

    private function find(int $id): array
    {
        return $this->model()->find($id) ?? throw PageNotFoundException::forPageNotFound();
    }

    private function validated(): ?array
    {
        $rules = [];
        $data = [];

        foreach ($this->fields() as $field) {
            $rules[$field['name']] = ['label' => $field['label'], 'rules' => $field['rules'] ?? 'permit_empty'];
        }

        if (!$this->validate($rules)) {
            return null;
        }

        foreach ($this->fields() as $field) {
            $value = $this->request->getPost($field['name']);

            if ($field['type'] === 'checkbox') {
                $data[$field['name']] = $value === '1' ? 1 : 0;
            } elseif ($value === null || $value === '') {
                if ($field['type'] !== 'number') {
                    $data[$field['name']] = null;
                }
            } else {
                $data[$field['name']] = $value;
            }
        }

        return $data;
    }
}
