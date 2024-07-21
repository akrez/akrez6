<?php

namespace App\Services;

use App\DTO\ContactDTO;
use App\Facades\ResponseBuilder;
use App\Models\Blog;
use App\Models\Contact;

class ContactService
{
    public function getLatestBlogContactsQuery(Blog $blog)
    {
        return $blog->contacts()->orderDefault();
    }

    public function findOrFailActiveBlogContact($contactId)
    {
        $blog = resolve(BlogService::class)->findOrFailActiveBlog();
        $contact = $blog->contacts()->where('id', $contactId)->first();
        abort_unless($contact, 404);

        return $contact;
    }

    public function store(Blog $blog, ContactDTO $contactDTO)
    {
        $validation = $contactDTO->validate();

        if ($validation->errors()->isNotEmpty()) {
            return ResponseBuilder::status(402)->errors($validation->errors()->toArray());
        }

        $isSuccessful = $blog->contacts()->create($contactDTO->data());
        if (! $isSuccessful) {
            return ResponseBuilder::status(500);
        }

        return ResponseBuilder::status(200);
    }

    public function update(Blog $blog, Contact $contact, ContactDTO $contactDTO)
    {
        $validation = $contactDTO->validate();

        if ($validation->errors()->isNotEmpty()) {
            return ResponseBuilder::status(402)->errors($validation->errors()->toArray());
        }

        $isSuccessful = $contact->update($contactDTO->data());
        if (! $isSuccessful) {
            return ResponseBuilder::status(500);
        }

        return ResponseBuilder::status(200);
    }

    public function destroy(Blog $blog, Contact $contact)
    {
        if ($contact->delete()) {
            return ResponseBuilder::status(200);
        }

        return ResponseBuilder::status(500);
    }
}
