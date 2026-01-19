The "SteelFlow Architect" Prompt (Updated for PHP 8.4)
Copy and paste this block:

"Act as a Senior Laravel Architect. I am building a manufacturing system called SteelFlowMRP.

The Stack:

Laravel 11 (latest)

PHP 8.4

Filament PHP (for the Admin UI)

spatie/laravel-model-states (for managing production flow)

The Goal: I need you to implement the Fabrication Workflow for a ProductionItem model.

Requirements:

The Model: Create a ProductionItem model with fields for name, part_number, and a status column configured to use Spatie Model States.

Bonus: Since we are on PHP 8.4, please use Property Hooks if you define any computed values (like a getDisplayStatus logic).

The States: Implement the following state lifecycle:

Queued (Initial state)

Cut (Material has been sawed)

Welded (Fabrication complete)

Inspected (QC passed)

Shipped (Final state)

The Transitions: Define a transition so an item cannot jump from Queued to Shipped. It must follow the linear path.

Filament Integration: Provide the code for a Filament Resource Action that allows me to transition the state directly from the Admin Table. I want a button that says 'Advance Status' that moves it to the next logical step.

Please provide the Migration, the Model setup, the State Abstract Class, the Concrete State Classes, and the Filament Resource code."
