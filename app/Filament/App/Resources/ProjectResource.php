<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ProjectResource\Pages;
use App\Helpers\PejotaHelper;
use App\Livewire\Projects\ListTasks;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Livewire;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema(
                self::getFormComponents()
            );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('active'),

                Tables\Columns\SpatieTagsColumn::make('tags'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->timezone(PejotaHelper::getUserTimeZone())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->timezone(PejotaHelper::getUserTimeZone())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('client')
                    ->relationship('client', 'name'),
                Tables\Filters\TernaryFilter::make('active'),
            ])
            ->groups([
                Tables\Grouping\Group::make('client.name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc')
            ->persistFiltersInSession();
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Split::make([
                    Section::make([
                        TextEntry::make('name')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->label(''),

                        SpatieTagsEntry::make('tags')
                            ->label(''),

                        TextEntry::make('client.name')
                            ->label('')
                            ->icon('heroicon-o-building-office'),

                        TextEntry::make('description')
                            ->formatStateUsing(fn (string $state): HtmlString => new HtmlString($state))
                            ->label('')
                            ->icon('heroicon-o-document-text'),

                    ]),

                    Section::make([
                        TextEntry::make('active')
                            ->formatStateUsing(fn (string $state): string => $state ? 'Yes' : 'No'),

                        TextEntry::make('created_at')
                            ->dateTime()
                            ->timezone(PejotaHelper::getUserTimeZone()),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->timezone(PejotaHelper::getUserTimeZone()),
                        Actions::make([
                            Action::make('edit')
                                ->url(
                                    fn (Model $record) => "{$record->id}/edit"
                                )
                                ->icon('heroicon-o-pencil'),

                            Action::make('back')
                                ->url(
                                    fn (Model $record) => './.'
                                )
                                ->icon('heroicon-o-chevron-left')
                                ->color(Color::Neutral),
                        ]),
                    ])->grow(false),

                ])
                    ->columnSpanFull(),

                Section::make('Tasks')
                    ->schema([
                        Livewire::make(
                            ListTasks::class,
                            fn (Model $record) => ['project' => $record]
                        ),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'view' => Pages\ViewProject::route('/{record}'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getFormComponents(): array
    {
        return [
            Forms\Components\Select::make('client')
                ->relationship('client', 'name')
                ->preload(),
            Forms\Components\TextInput::make('name')
                ->required(),
            Forms\Components\RichEditor::make('description')
                ->columnSpanFull()
                ->fileAttachmentsDisk('projects')
                ->fileAttachmentsDirectory(auth()->user()->company->id)
                ->fileAttachmentsVisibility('private'),
            Forms\Components\SpatieTagsInput::make('tags'),
            Forms\Components\Toggle::make('active')
                ->required()
                ->default(true),
        ];
    }
}
