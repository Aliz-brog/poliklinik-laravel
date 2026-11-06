<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    protected $table = 'poli';

    public $timestamps = false;

    protected $fillable = [
        'nama_poli',
        'keterangan',
    ];

    public function dokters()
    {
        return $this->hasMany(User::class, 'id_poli');
    }

    public function descriptionIsContainedIn(string $text): bool
    {
        $normalize = static fn (string $value): string => trim(
            preg_replace('/[^\pL\pN]+/u', ' ', mb_strtolower($value)) ?? '',
        );
        $description = $normalize((string) $this->keterangan);

        return $description !== '' && str_contains($normalize($text), $description);
    }
}
