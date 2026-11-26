<?php

namespace App\Filament\Resources\Litters\Pages;

use App\Filament\Resources\Litters\LitterResource;
use App\Filament\Resources\Pigs\PigResource;
use App\Models\Cage;
use App\Models\Pig;
use App\Rules\OwnedByCurrentUser;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Unique;

class RegisterPiglets extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = LitterResource::class;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        static::getResource()::authorizeView($this->getRecord());

        $this->fillPigletForm();
    }

    public function getTitle(): string | Htmlable
    {
        return 'Keep as piglets';
    }

    public function getHeading(): string | Htmlable
    {
        $litter = $this->getRecord();

        return 'Keep as piglets — '.($litter->litter_tag ?: 'Litter #'.$litter->getKey());
    }

    public function getSubheading(): ?string
    {
        $litter = $this->getRecord();

        return sprintf(
            '%d alive, %d registered, %d left to register.',
            $litter->current_count,
            $litter->piglets()->count(),
            $this->remainingCount(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Alive piglets')
                    ->description('Register each piglet individually. Remove any rows for piglets you are not registering yet.')
                    ->schema([
                        Repeater::make('piglets')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Tag'),
                                TableColumn::make('Sex'),
                                TableColumn::make('Cage'),
                                TableColumn::make('Notes'),
                            ])
                            ->schema([
                                TextInput::make('tag_number')
                                    ->label('Tag')
                                    ->required()
                                    ->maxLength(50)
                                    ->distinct()
                                    ->unique(
                                        table: 'pigs',
                                        column: 'tag_number',
                                        modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('user_id', auth()->id()),
                                    ),

                                Select::make('sex')
                                    ->label('Sex')
                                    ->options([
                                        'female' => 'Female',
                                        'male' => 'Male',
                                    ])
                                    ->default('female')
                                    ->required()
                                    ->native(false),

                                Select::make('cage_id')
                                    ->label('Cage')
                                    ->options(fn (): array => Cage::query()
                                        ->orderBy('cage_number')
                                        ->pluck('cage_number', 'id')
                                        ->all())
                                    ->searchable()
                                    ->native(false)
                                    ->rules([new OwnedByCurrentUser(Cage::class)]),

                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(1),
                            ])
                            ->maxItems(fn (): int => max(1, $this->remainingCount()))
                            ->minItems(1)
                            ->addActionLabel('Add another piglet')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (): bool => $this->remainingCount() > 0),

                Callout::make('Every alive piglet is registered')
                    ->description('You can still edit individual piglets from the Pigs page.')
                    ->color('success')
                    ->visible(fn (): bool => $this->remainingCount() === 0),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
                EmbeddedTable::make(),
            ]);
    }

    protected function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('register')
            ->footer([
                Actions::make([
                    Action::make('register')
                        ->label('Register piglets')
                        ->icon('heroicon-o-check-circle')
                        ->submit('register'),
                ]),
            ])
            ->visible(fn (): bool => $this->remainingCount() > 0);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Registered piglets')
            ->query(fn (): Builder => Pig::query()->where('litter_id', $this->getRecord()->getKey()))
            ->columns([
                TextColumn::make('tag_number')
                    ->label('Tag')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('sex')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'female' ? 'info' : 'warning'),

                TextColumn::make('cage.cage_number')
                    ->label('Cage')
                    ->badge()
                    ->placeholder('Unassigned'),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === Pig::STATUS_ACTIVE ? 'success' : 'gray'),

                TextColumn::make('notes')
                    ->limit(40)
                    ->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Pig $record): string => PigResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('No piglets registered yet')
            ->emptyStateDescription('Register the first piglet using the form above.')
            ->paginated(false);
    }

    public function register(): void
    {
        $litter = $this->getRecord();

        $data = $this->form->getState();

        $rows = collect($data['piglets'] ?? [])
            ->filter(fn (array $row): bool => filled($row['tag_number'] ?? null))
            ->values();

        if ($rows->isEmpty()) {
            Notification::make()
                ->title('No piglets to register')
                ->body('Add at least one tag number.')
                ->warning()
                ->send();

            return;
        }

        if ($rows->count() > $this->remainingCount()) {
            Notification::make()
                ->title('Too many piglets')
                ->body('Only '.$this->remainingCount().' alive piglet(s) still need registering.')
                ->danger()
                ->send();

            return;
        }

        $tags = $rows
            ->pluck('tag_number')
            ->map(fn (string $tag): string => trim($tag));

        if ($tags->duplicates()->isNotEmpty()) {
            Notification::make()
                ->title('Duplicate tag numbers')
                ->body('Each piglet needs its own tag number.')
                ->danger()
                ->send();

            return;
        }

        DB::transaction(function () use ($litter, $rows): void {
            foreach ($rows as $row) {
                $litter->registerPiglet([
                    'tag_number' => trim($row['tag_number']),
                    'sex' => $row['sex'] ?? 'female',
                    'cage_id' => $row['cage_id'] ?? null,
                    'notes' => $row['notes'] ?? null,
                ]);
            }
        });

        $litter->refresh();

        Notification::make()
            ->title($rows->count().' piglet(s) registered')
            ->body('They now appear in the Pigs list and below.')
            ->success()
            ->send();

        $this->fillPigletForm();
        $this->resetTable();
    }

    /**
     * Start with one blank row for each alive piglet still to register.
     */
    protected function fillPigletForm(): void
    {
        $rows = array_fill(0, $this->remainingCount(), ['sex' => 'female']);

        $this->form->fill([
            'piglets' => $rows,
        ]);
    }

    protected function remainingCount(): int
    {
        $litter = $this->getRecord();

        return max(0, (int) $litter->current_count - $litter->piglets()->count());
    }
}
