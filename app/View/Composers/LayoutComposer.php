<?php

namespace App\View\Composers;

use Illuminate\View\View;

class LayoutComposer
{
    public function compose(View $view): void
    {
        $request = request();
        if (! $request->attributes->has('layout_data')) {
            $user = $request->user();
            $request->attributes->set('layout_data', [
                'cartCount' => $user ? $user->cartItems()->count() : 0,
                'activeAttempt' => $user && ! $user->isAdmin() ? $user->activeAttempt() : null,
                'unreadCount' => $user ? $user->unreadNotifications()->count() : 0,
            ]);
        }

        $view->with($request->attributes->get('layout_data'));
    }
}
