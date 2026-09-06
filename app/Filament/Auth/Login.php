<?php

namespace App\Filament\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as ViewComponent;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getCaptchaFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getCaptchaFormComponent(): Component
    {
        return Section::make('Verifikasi Keamanan')
            ->description('Masukkan kode yang tertera pada gambar di bawah ini.')
            ->compact()
            ->schema([
                ViewComponent::make('filament.auth.captcha'),
                TextInput::make('captcha')
                    ->label('Kode Verifikasi')
                    ->required()
                    ->rule('captcha')
                    ->autocomplete('off')
                    ->extraInputAttributes(['autocapitalize' => 'off']),
            ]);
    }
}
