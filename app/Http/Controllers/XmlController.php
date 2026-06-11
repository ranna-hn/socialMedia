<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportXmlRequest;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use SimpleXMLElement;

class XmlController extends Controller
{
    public function exportPosts(): Response
    {
        $posts = Post::query()
            ->with('user', 'group')
            ->latest()
            ->limit(500)
            ->get();

        $xml = new SimpleXMLElement('<posts/>');

        foreach ($posts as $post) {
            $node = $xml->addChild('post');
            $node->addChild('id', (string) $post->id);
            $node->addChild('body', htmlspecialchars(strip_tags($post->body)));
            $node->addChild('user_email', $post->user?->email ?? '');
            $node->addChild('group_slug', $post->group?->slug ?? '');
            $node->addChild('created_at', $post->created_at?->toIso8601String() ?? '');
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="echonature-posts.xml"',
        ]);
    }

    public function exportUsers(): Response
    {
        abort_unless(request()->user()->isAdmin(), 403);

        $users = User::query()->orderBy('name')->limit(1000)->get();
        $xml = new SimpleXMLElement('<users/>');

        foreach ($users as $user) {
            $node = $xml->addChild('user');
            $node->addChild('name', htmlspecialchars($user->name));
            $node->addChild('username', htmlspecialchars($user->username));
            $node->addChild('email', htmlspecialchars($user->email));
            $node->addChild('role', $user->role);
        }

        return response($xml->asXML(), 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="echonature-users.xml"',
        ]);
    }

    public function import(ImportXmlRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $xml = simplexml_load_file($request->file('xml')->getRealPath());

        abort_unless($xml instanceof SimpleXMLElement, 422, __('econature.xml.invalid'));

        $data['type'] === 'users'
            ? $this->importUsers($xml)
            : $this->importPosts($xml, $request->user());

        return back()->with('success', __('econature.xml.imported'));
    }

    private function importPosts(SimpleXMLElement $xml, User $fallbackUser): void
    {
        DB::transaction(function () use ($xml, $fallbackUser): void {
            foreach ($xml->post as $node) {
                $email = (string) $node->user_email;
                $user = $email ? User::where('email', $email)->first() : null;

                Post::create([
                    'body' => '<p>'.e((string) $node->body).'</p>',
                    'user_id' => $user?->id ?? $fallbackUser->id,
                    'group_id' => null,
                ]);
            }
        });
    }

    private function importUsers(SimpleXMLElement $xml): void
    {
        DB::transaction(function () use ($xml): void {
            foreach ($xml->user as $node) {
                $email = (string) $node->email;

                if (! $email) {
                    continue;
                }

                User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => (string) $node->name ?: Str::before($email, '@'),
                        'username' => (string) $node->username ?: Str::slug(Str::before($email, '@')),
                        'role' => (string) $node->role === User::ROLE_ADMIN ? User::ROLE_ADMIN : User::ROLE_USER,
                        'password' => Hash::make(Str::random(32)),
                        'email_verified_at' => now(),
                    ]
                );
            }
        });
    }
}
